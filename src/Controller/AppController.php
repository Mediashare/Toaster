<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Hub;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $hubs = $em->getRepository(Hub::class)->findBy([], ['updateDate' => 'DESC']);
        
        return $this->render('index.html.twig', [
            'hubs' => $hubs
        ]);
    }
}