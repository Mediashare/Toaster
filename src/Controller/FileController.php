<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\File;

class FileController extends AbstractController
{
    /**
     * @Route("/show/file/{token}", name="file_show")
     */
    public function show(string $token) {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->findOneBy(['token' => $token]);
        return $this->render('file/show.html.twig', [
            'file' => $file
        ]);
    }

    /**
     * @Route("/edit/file/{token}", name="file_edit")
     */
    public function edit(Request $request, string $token) {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->findOneBy(['token' => $token]);
        if (!$file || $this->getUser() != $file->getUser()):
            return $this->redirectToRoute('app');
        endif;
        
        if ($request->isMethod('POST')) {
            $file->setFilename($request->request->get('filename'));
            $file->setDescription($request->request->get('edit_file_description'));
            $file->setUpdateDate(new \DateTime());
            $em->persist($file);
            $em->flush();

            return $this->redirectToRoute('file_show', ['token' => $token]);
        }

        return $this->render('file/edit.html.twig', [
            'file' => $file
        ]);
    }

    /**
     * @Route("/download/file/{token}", name="file_download")
     */
    public function download(string $token) {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->findOneBy(['token' => $token]);
        $response = new BinaryFileResponse($file->getPath());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getFilename()
        );
        return $response;
    }

    /**
     * @Route("/remove/file/{token}", name="file_remove")
     */
    public function remove(string $token) {
        $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository(File::class)->findOneBy(['token' => $token]);
        if (!$file || $this->getUser() != $file->getUser()):
            return $this->redirectToRoute('app');
        endif;
        
        $stockage = $file->getStockage();
        $stockage->removeFile($file);
        $em->remove($file);
        $em->flush();

        return $this->redirectToRoute('stockage_show', ['token' => $stockage->getToken()]);
    }
}