<?php
namespace App\Controller;

use App\Entity\Hub;
use App\Entity\File;
use Chumper\Zipper\Zipper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HubController extends AbstractController
{
    /**
     * @Route("/hubs", name="hub")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $hubs = $em->getRepository(Hub::class)->findBy([], ['updateDate' => 'DESC']);

        return $this->render('hub/index.html.twig', [
            'hubs' => $hubs
        ]);
    }

    /**
     * @Route("/show/hub/{token}", name="hub_show")
     */
    public function show(string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $hub = $em->getRepository(Hub::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        $files = $em->getRepository(File::class)->findBy(['hub' => $hub], ['updateDate' => 'DESC']);
        
        return $this->render('hub/show.html.twig', [
            'hub' => $hub,
            'files' => $files
        ]);
    }

    /**
     * @Route("/edit/hub/{token}", name="hub_edit")
     */
    public function edit(Request $request, string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $hub = $em->getRepository(Hub::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        $files = $em->getRepository(File::class)->findBy(['hub' => $hub], ['updateDate' => 'DESC']);
        
        if (!$hub || $this->getUser() != $hub->getUser()):
            return $this->redirectToRoute('app');
        endif;
        
        if ($request->isMethod('POST') && $request->get('name')) {
            $hub->setName($request->get('name'));
            $hub->setDescription($request->get('edit_hub_description'));
            $hub->setUpdateDate(new \DateTime());
            $em->persist($hub);
            $em->flush();

            return $this->redirectToRoute('hub_show', ['token' => $token]);
        }

        return $this->render('hub/edit.html.twig', [
            'hub' => $hub,
            'files' => $files
        ]);
    }

    /**
     * @Route("/archive/hub/{token}", name="hub_archive")
     */
    public function archive(string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $hub = $em->getRepository(Hub::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        
        if (count($hub->getFiles()) < 1):
            return $this->redirectToRoute('hub_show', ['token' => $hub->getToken()]);
        endif;
        // Create File
        $archive = new File();
        $archive->setHub($hub);
        $archive->setFilename($hub->getToken().'.zip');
        $archive->setMetadata(['extension' => 'zip', 'mimeType' => 'application/zip']);
            
        // Compress files
        foreach ($hub->getFiles() as $file) {
            $files[] = $file->getPath();
        }
        $zipper = new Zipper();
        $zipPath = $hub->getPath().'/'.$hub->getToken().'.zip';
        $zipper->make($zipPath);
        $zipper->add($files);
        $zipper->close();

        $response = new BinaryFileResponse($zipPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $hub->getToken().'.zip'
        );
        // $archive->remove();
        return $response;
    }

    /**
     * @Route("/remove/hub/{token}", name="hub_remove")
     */
    public function remove(string $token)
    {
        $em = $this->getDoctrine()->getManager();
        $hub = $em->getRepository(Hub::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
        if (!$hub || $this->getUser() != $hub->getUser()):
            return $this->redirectToRoute('app');
        endif;

        foreach ($hub->getFiles() as $file) {
            $hub->removeFile($file);
            $em->remove($file);
            $em->flush();
        }
        $hub->remove();
        $em->remove($hub);
        $em->flush();
        
        return $this->redirectToRoute('app');
    }
}