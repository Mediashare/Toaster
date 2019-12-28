<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Stockage;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $stockages = $em->getRepository(Stockage::class)->findBy([], ['updateDate' => 'DESC']);
        
        return $this->render('index.html.twig', [
            'stockages' => $stockages
        ]);
    }
}