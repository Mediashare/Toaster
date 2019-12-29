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
        $tags = $em->getRepository(Tag::class)->findBy([], ['updateDate' => 'DESC']);

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
        $tag = $em->getRepository(Tag::class)->findOneBy(['slug' => $slug], ['updateDate' => 'DESC']);
        $files = $em->getRepository(File::class)->findByTag($tag);
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
            'files' => $files,
        ]);
    }


    public function getLastFiles(Tag $tag) {
        $em = $this->getDoctrine()->getManager();
        $files = $em->getRepository(File::class)->findByTag($tag);
        return $this->render('file/_files.html.twig', [
            'files' => $files
        ]);
    }
}