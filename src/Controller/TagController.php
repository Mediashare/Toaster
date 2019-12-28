<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Hub;
use App\Entity\File;
use App\Entity\Tag;

class TagController extends AbstractController
{
    /**
     * @Route("/tags", name="tag")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository(Tag::class)->search();

        return $this->render('tag/index.html.twig', [
            'tags' => $tags
        ]);
    }
    /**
     * @Route("/show/tag/{slug}", name="tag_show")
     */
    public function show(string $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $tags = $em->getRepository(Tag::class)->search($slug);
        
        $hubs = [];
        foreach ($tags as $tag) {
            $files = $em->getRepository(File::class)->findByTag($tag);
            foreach ($files as $file) {
                $hub = $file->getHub();
                $hubs[$hub->getToken()]['token'] = $hub->getToken();
                $hubs[$hub->getToken()]['name'] = $hub->getName();
                $hubs[$hub->getToken()]['files'][] = $file;
                
            }
        }
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
            'hubs' => $hubs,
        ]);
    }
}