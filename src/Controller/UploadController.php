<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Stockage;
use App\Entity\Tag;
use App\Entity\File;

class UploadController extends AbstractController
{
    private $em;
    public function __construct() {
        $this->filesystem = new Filesystem();
    }
    
    /**
     * @Route("/upload/file", name="upload_file")
     */
    public function form(Request $request) {
        $this->em = $this->getDoctrine()->getManager();
        $files = (array) $request->files->get('files');
        if ($request->isMethod('POST') && $files && count($files) > 0) {
            $files_description = $request->request->get('files_description');
            $files = $this->getFileUploaded($files, $files_description);
            $tags = $this->getTags($request->request->get('tags'));
            $stockages = $this->getStockages($request->request->get('stockages'));
            foreach ($stockages as $stockage) {
                $tmp_files = $this->recordFiles($stockage, $tags, $files);
            }
            // Remove tmp files
            foreach ($tmp_files as $tmp_file) {
                $this->filesystem->remove($tmp_file['filepath']);
            }
            return $this->redirectToRoute('stockage_show', ['token' => $stockage->getToken()]);
        }
        
        $stockages = $this->em->getRepository(Stockage::class)->findAll();
        $tags = $this->em->getRepository(Tag::class)->findAll();
        return $this->render('file/_form.html.twig', [
            'stockages' => $stockages,
            'tags' => $tags
        ]);
    }

    /**
     * Convert UploadedFile Object to Array
     *
     * @param array $files
     * @return array $files
     */
    private function getFileUploaded(array $files_uploaded = null, string $files_description = null) {
        $files = [];
        foreach ($files_uploaded as $file_uploaded) {
            if (!empty((string) $file_uploaded) && !$file_uploaded->getError()):
                $data = array_values((array) $file_uploaded);
                $files[] = [
                    'filename' =>  $data[1],
                    'mimeType' => $data[2],
                    'extension' => pathinfo($data[1])['extension'] ?? null,
                    'filepath' => (string) $file_uploaded,
                    'description' => (string) $files_description,
                ];
            endif;
        }
        return $files;
    } 

    private function getStockages(string $stockages) {
        // New Stockage
        $stockages = (array) explode(',', $stockages);
        foreach ($stockages as $index => $name) {
            $stockage = $this->em->getRepository(Stockage::class)->findOneBy(['name' => $name], ['updateDate' => 'DESC']);
            if (!$stockage):
                $stockage = new Stockage();
                $stockage->setName($name);
                $stockage->setPath($this->getParameter('stockage'));
                if ($this->getUser()):
                    $stockage->setUser($this->getUser());
                endif;
            endif;
            $stockage->setUpdateDate(new \DateTime());
            unset($stockages[$index]);
            $stockages[] = $stockage;
        }
        return $stockages;
    }

    private function getTags(string $tags) {
        // New Stockage
        $tags = (array) explode(',', $tags);
        foreach ($tags as $index => $name) {
            $tag = $this->em->getRepository(Tag::class)->findOneBy(['name' => $name], ['updateDate' => 'DESC']);
            if (!$tag):
                $tag = new Tag();
                $tag->setName($name);
            endif;
            unset($tags[$index]);
            $tags[] = $tag;
        }
        return $tags;
    }

    private function recordFiles(Stockage $stockage, array $tags, array $files) {
        foreach ($files as $file_data) {
            // Set file data
            $file = new File();
            $file->setFilename($file_data['filename']);
            $file->setDescription($file_data['description']);
            $file->setMetadata($file_data);
            // Stockage
            $file->setStockage($stockage);
            // Tags
            foreach ($tags as $tag) {
                $file->addTag($tag);
            }
            // User
            if ($this->getUser()):
                $file->setUser($this->getUser());
            endif;

            // Copy tmp file
            $filePath = $file->getPath();
            $this->filesystem->copy($file_data['filepath'], $filePath);

            // Check duplicata
            $checksum = $file->setChecksum();
            if ($checksum):
                // Record file
                $this->em->persist($file);
                $this->em->flush();
            endif;
        }

        return $files;
    }
}