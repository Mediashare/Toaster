<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Stockage;
use App\Entity\File;
use Chumper\Zipper\Zipper;

class StockageController extends AbstractController
{
    /**
     * @Route("/stockages", name="stockage")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $stockages = $em->getRepository(Stockage::class)->findBy([], ['updateDate' => 'DESC']);

        return $this->render('stockage/index.html.twig', [
            'stockages' => $stockages
        ]);
    }

    /**
     * @Route("/show/stockage/{token}", name="stockage_show")
     */
    public function show(string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $stockage = $em->getRepository(Stockage::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        $files = $em->getRepository(File::class)->findBy(['stockage' => $stockage], ['updateDate' => 'DESC']);
        
        return $this->render('stockage/show.html.twig', [
            'stockage' => $stockage,
            'files' => $files
        ]);
    }

    /**
     * @Route("/edit/stockage/{token}", name="stockage_edit")
     */
    public function edit(Request $request, string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $stockage = $em->getRepository(Stockage::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        $files = $em->getRepository(File::class)->findBy(['stockage' => $stockage], ['updateDate' => 'DESC']);
        
        if (!$stockage || $this->getUser() != $stockage->getUser()):
            return $this->redirectToRoute('app');
        endif;
        
        if ($request->isMethod('POST')) {
            $stockage->setName($request->request->get('name'));
            $stockage->setDescription($request->request->get('edit_stockage_description'));
            $stockage->setUpdateDate(new \DateTime());
            $em->persist($stockage);
            $em->flush();

            return $this->redirectToRoute('stockage_show', ['token' => $token]);
        }

        return $this->render('stockage/edit.html.twig', [
            'stockage' => $stockage,
            'files' => $files
        ]);
    }

    /**
     * @Route("/archive/stockage/{token}", name="stockage_archive")
     */
    public function archive(string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $stockage = $em->getRepository(Stockage::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        
        if (count($stockage->getFiles()) < 1):
            return $this->redirectToRoute('stockage_show', ['token' => $stockage->getToken()]);
        endif;
        // Create File
        $archive = new File();
        $archive->addStockage($stockage);
        $archive->setFilename($stockage->getSlug().'-'.$archive->getToken().'.zip');
        $archive->setMetadata(['extension' => 'zip', 'mimeType' => 'application/zip']);
            
        // Compress files
        foreach ($stockage->getFiles() as $file) {
            $files[] = $file->getPath();
        }

        $zipper = new Zipper();
        $zipper->make($archive->getPath());
        $zipper->add($files);
        $zipper->close();

        $checksum = $archive->setChecksum();
        if ($checksum):
            $em->persist($archive);
            $em->flush();
        endif;
        
        return $this->redirectToRoute('file_show', ['token' => $archive->getToken()]);
    }

    /**
     * @Route("/remove/stockage/{token}", name="stockage_remove")
     */
    public function remove(string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $stockage = $em->getRepository(Stockage::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        if (!$stockage || $this->getUser() != $stockage->getUser()):
            return $this->redirectToRoute('app');
        endif;

        foreach ($stockage->getFiles() as $file) {
            $stockage->removeFile($file);
            $em->remove($file);
            $em->flush();
        }
        $stockage->remove();
        $em->remove($stockage);
        $em->flush();
        
        return $this->redirectToRoute('app');
    }
}