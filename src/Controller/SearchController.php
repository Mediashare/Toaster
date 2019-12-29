<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Hub;
use App\Entity\File;
use App\Entity\Tag;

class SearchController extends AbstractController
{
    public function searchBar()
    {
        $em = $this->getDoctrine()->getManager();
        $hubs = $em->getRepository(Hub::class)->findBy([], ['updateDate' => 'DESC']);
        
        return $this->render('_search.html.twig', [
            'hubs' => $hubs
        ]);
    }

    /**
     * @Route("/api/search", name="search")
     */
    public function search(Request $request)
    {
        $query = $request->request->get('query');
        if (!$query):
            $query = $request->get('query');
        endif;
        $query = \strtolower($query);

        $data['results'] = [];
        $em = $this->getDoctrine()->getManager();
        $hubs = $em->getRepository(Hub::class)->findBy([], ['updateDate' => 'DESC']);
        foreach ($hubs as $hub) {
            if (\strpos(\strtolower($hub->getName()), $query) !== false):
                $data['results']['Hubs']['name'] = 'Hubs';
                $data['results']['Hubs']['results'][] = [
                    'name' => 'Hubs',
                    'title' => $hub->getName(),
                    'url' => $this->generateUrl('hub_show', ['token' => $hub->getToken()]),
                ];
            endif;
            foreach ($hub->getFiles() as $file) {
                if (\strpos(\strtolower($file->getFilename()), $query) !== false):
                    $data['results']['Files']['name'] = 'Files';
                    if (\strpos($file->getMetadata()['mimeType'], 'image/') !== false):
                        // With Image
                        $data['results']['Files']['results'][] = [
                            'name' => 'Files',
                            'title' =>  $file->getHub()->getName().' / '.$file->getFilename(),
                            'description' => $file->getDescription(),
                            'hub' => $hub->getName(),
                            'url' => $this->generateUrl('file_show', ['token' => $file->getToken()]),
                            'image' => $this->generateUrl('file_download', ['token' => $file->getToken()]),
                        ];
                    else:
                        // Without Image
                        $data['results']['Files']['results'][] = [
                            'name' => 'Files',
                            'title' =>  $file->getHub()->getName().' / '.$file->getFilename(),
                            'description' => $file->getDescription(),
                            'hub' => $hub->getName(),
                            'url' => $this->generateUrl('file_show', ['token' => $file->getToken()]),
                        ];
                    endif;
                endif;
                foreach ($file->getTags() as $tag) {
                    if (\strpos(\strtolower('#'.$tag->getName()), $query) !== false):
                        $tags[$tag->getName()] = $tag;
                    endif;
                }
            }
        }
        foreach ($tags as $tag) {
            $data['results']['Tags']['name'] = 'Tags';
            $data['results']['Tags']['results'][] = [
                'name' => 'Tags',
                'title' => '#'.$tag->getName(),
                'url' => $this->generateUrl('tag_show', ['slug' => $tag->getSlug()]),
            ];
        }
        return new JsonResponse($data, 200);
    }
}