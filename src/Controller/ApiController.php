<?php

namespace App\Controller;

use App\Entity\Hub;
use App\Entity\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends Controller
{
    /**
     * @Route("/api/hub/{token}", name="api_hub")
     */
    public function hub(string $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Hub::class);
        if ($token):
            $hub = $repo->findOneBy(['token' => $token]);
            $json = $hub;
        else:
            $hubs = $repo->findBy(['token' => $token]);
            $json = $hubs;
        endif;
        
        return new JsonResponse($json, 200);
    }

    /**
     * @Route("/api/file/{token}", name="api_file")
     */
    public function file(string $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(File::class);
        if ($token):
            $file = $repo->findOneBy(['token' => $token]);
            $json = $file;
        else:
            $files = $repo->findBy(['token' => $token]);
            $json = $files;
        endif;
        
        return new JsonResponse($json, 200);
    }
}
