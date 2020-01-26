<?php
namespace App\Controller;

use App\Entity\Hub;
use App\Entity\Tag;
use App\Entity\File;
use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UploadController extends AbstractController
{
    private $em;
    public function __construct() {
        $this->filesystem = new Filesystem();
    }
    
    public function form() {
        $this->em = $this->getDoctrine()->getManager();
        $hubs = $this->em->getRepository(Hub::class)->findAll();
        $tags = $this->em->getRepository(Tag::class)->findAll();
        return $this->render('file/_form.html.twig', [
            'hubs' => $hubs,
            'tags' => $tags
        ]);
    }
    
    /**
     * @Route("/upload/file", name="upload_file")
     */
    public function upload(Request $request) {
        $this->em = $this->getDoctrine()->getManager();

        // Check client type (browser/terminal)
        if ($request->get('formulaire') === "true"): // Browser
            if ($this->getUser()): // Check if User
                $result = $this->process($request, $this->getUser());
                if ($result):
                    return $this->redirectToRoute('hub_show', ['token' => $result]);
                endif;
            endif;
            // Return Error
            $session = new Session();
            $session->getFlashBag()->add('error', 'Upload form have problem.');
            return $this->redirectToRoute('app');
        else: // Terminal
            $api_key = $request->get('api_key');
            $user = $this->em->getRepository(User::class)->findOneBy(['apiKey' => $api_key]);
            if ($user):
                $result = $this->process($request, $user);
                if ($result):
                    $response = [
                        'status' => 'success',
                        'message' => 'File was uploaded.'
                    ];
                else:
                    $response = [
                        'status' => 'error',
                        'message' => 'File was not submited.'
                    ];
                endif;
            else:
                $response = [
                    'status' => 'error',
                    'message' => 'Invalid api_key submited.'
                ];
            endif;
            return new JsonResponse($response, 200);
        endif;
    }

    /**
     * Process for uploading file
     *
     * @param Request $request
     * @param User $user
     * @return bool
     */
    public function process(Request $request, User $user) {
        $files = (array) $request->files->get('files');
        if ($request->isMethod('POST') && $files && count($files) > 0) {
            $files_description = $request->request->get('files_description');
            $files = $this->getFileUploaded($files, $files_description);
            if (!empty($request->request->get('tags'))):
                $tags = $this->getTags($request->request->get('tags'));
            else:
                $tags = [];
            endif;
            $hubs = $this->getHubs($request->request->get('hubs'), $user);
            foreach ($hubs as $hub) {
                $tmp_files = $this->recordFiles($hub, $tags, $files, $user);
            }
            // Remove tmp files
            foreach ($tmp_files as $tmp_file) {
                $this->filesystem->remove($tmp_file['filepath']);
            }
            // Success
            return $hub->getToken(); 
        }
        // Error
        return false;
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

    private function getHubs(string $hubs, User $user) {
        // New Hub
        $hubs = (array) explode(',', $hubs);
        foreach ($hubs as $index => $name) {
            $hub = $this->em->getRepository(Hub::class)->findOneBy(['name' => $name], ['updateDate' => 'DESC']);
            if (!$hub):
                $hub = new Hub();
                $hub->setName($name);
                $hub->setPath();
                $hub->setUser($user);
            endif;
            $hub->setUpdateDate(new \DateTime());
            unset($hubs[$index]);
            $hubs[] = $hub;
        }
        return $hubs;
    }

    private function getTags(string $tags) {
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

    private function recordFiles(Hub $hub, array $tags, array $files, User $user) {
        foreach ($files as $file_data) {
            // Set file data
            $file = new File();
            $file->setFilename($file_data['filename']);
            $file->setDescription($file_data['description']);
            $file->setMetadata($file_data);
            // Hub
            $file->setHub($hub);
            // Tags
            foreach ($tags as $tag) {
                $file->addTag($tag);
            }
            // User
            $file->setUser($user);

            // Copy tmp file
            $filePath = $this->getParameter('stockage').'/'.$file->getPath();
            $this->filesystem->copy($file_data['filepath'], $filePath);

            // Check duplicata
            $checksum = $file->setChecksum($this->getParameter('stockage'));
            if ($checksum):
                // Record file
                $this->em->persist($file);
                $this->em->flush();
            endif;
        }

        return $files;
    }
}