<?php
namespace App\Controller;

use App\Entity\Hub;
use App\Entity\Tag;
use App\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $file = $em->getRepository(File::class)->findOneBy(['token' => $token, 'user' => $this->getUser()]);
        if (!$file):
            return $this->redirectToRoute('app');
        endif;
        
        if ($request->isMethod('POST') && $request->get('filename')) {
            $file->setFilename($request->get('filename'));
            $file->setDescription($request->get('edit_file_description'));
            $file->removeTags();
            if (!empty($request->get('tags'))):
                $tags = $this->getTags($request->get('tags'));
                foreach ($tags as $tag) {
                    $file->addTag($tag);
                }
            endif;
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
        $response = new BinaryFileResponse($this->getParameter('stockage').'/'.$file->getPath());
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
        $file = $em->getRepository(File::class)->findOneBy(['token' => $token, 'user' => $this->getUser()]);
        if (!$file):
            return $this->redirectToRoute('app');
        endif;
        $file->remove($this->getParameter('stockage'));
        foreach ($file->getLikes() as $like) {
            $file->removeLike($like);
        }
        
        $hub = $file->getHub();
        $hub->removeFile($file);
        $em->remove($file);
        $em->flush();

        return $this->redirectToRoute('hub_show', ['token' => $hub->getToken()]);
    }


    private function getHubs(string $hubs) {
        // New Hub
        $em = $this->getDoctrine()->getManager();
        $hubs = (array) explode(',', $hubs);
        foreach ($hubs as $index => $name) {
            $hub = $em->getRepository(Hub::class)->findOneBy(['name' => $name], ['updateDate' => 'DESC']);
            if (!$hub):
                $hub = new Hub();
                $hub->setName($name);
                $hub->setPath($this->getParameter('stockage'));
                if ($this->getUser()):
                    $hub->setUser($this->getUser());
                endif;
            endif;
            $hub->setUpdateDate(new \DateTime());
            unset($hubs[$index]);
            $hubs[] = $hub;
        }
        return $hubs;
    }

    private function getTags(string $tags) {
        $em = $this->getDoctrine()->getManager();
        $tags = (array) explode(',', $tags);
        foreach ($tags as $index => $name) {
            $tag = $em->getRepository(Tag::class)->findOneBy(['name' => $name], ['updateDate' => 'DESC']);
            if (!$tag):
                $tag = new Tag();
                $tag->setName($name);
            endif;
            unset($tags[$index]);
            $tags[] = $tag;
        }
        return $tags;
    }
}