<?php

namespace App\Controller;

use App\Entity\Hub;
use App\Entity\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/hub/{token}", name="api_hub")
     */
    public function hubAction(string $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($token):
            $repo = $em->getRepository(Hub::class)->findOneBy(['token' => $token]);
            $repo->getUser()->files = null;
            $repo->getUser()->hubs = null;
            $repo->getUser()->likes = null;
            $repo->getUser()->comments = null;
        else:
            $repo = $em->getRepository(Hub::class)->findAll();
            foreach ($repo as $object) {
                $object->getUser()->files = null;
                $object->getUser()->hubs = null;
                $object->getUser()->likes = null;
                $object->getUser()->comments = null;
            }
        endif;
        $json = $this->serializer($repo);

        if (!$json):
            $json = ['error' => 'No result.'];
        endif;
        
        return new JsonResponse($json, 200);
    }

    /**
     * @Route("/api/file/{token}", name="api_file")
     */
    public function fileAction(string $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($token):
            $repo = $em->getRepository(File::class)->findOneBy(['token' => $token]);
            $repo->getUser()->files = null;
            $repo->getUser()->hubs = null;
            $repo->getUser()->likes = null;
            $repo->getUser()->comments = null;
        else:
            $repo = $em->getRepository(File::class)->findAll();
            foreach ($repo as $object) {
                $object->getUser()->files = null;
                $object->getUser()->hubs = null;
                $object->getUser()->likes = null;
                $object->getUser()->comments = null;
            }
        endif;
        $json = $this->serializer($repo);

        if (!$json):
            $json = ['error' => 'No result.'];
        endif;
        
        return new JsonResponse($json, 200);
    }
    
    private function serializer($repo) {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        
        $json = $serializer->serialize($repo, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        $json = json_encode(json_decode($json, true));

        return $json;
    }
}
