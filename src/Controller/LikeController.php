<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Like;
use App\Entity\Hub;
use App\Entity\File;
use App\Entity\Comment;

class LikeController extends AbstractController
{
    /**
     * @Route("/like/{type}/{token}", name="like")
     */
    public function like(string $type, string $token)
    {
        $result = $this->getEntity($type, $token);

        // Unlike
        $unliked = false;
        foreach ($result['entity']->getLikes() as $like) {
            if ($this->getUser() === $like->getUser()):
                $result['entity']->removeLike($like);
                $unliked = true;
            endif;
        }

        // Like
        if (!$unliked):
            $like = new Like();
            $like->setUser($this->getUser());
            $result['entity']->addLike($like);
        endif;

        $em = $this->getDoctrine()->getManager();
        $em->persist($like);
        // $em->persist($result['entity']);
        $em->flush();

        return $this->redirectToRoute($result['route'], $result['parameters']);
    }

    /**
     * Find the good entity.
     *
     * @param string $type ['hub', 'file', 'comment']
     * @param string $token
     * @return array $entity, $route, $parameters
     */
    private function getEntity(string $type, string $token) {
        $em = $this->getDoctrine()->getManager();
        if ($type === "hub"):
            $entity = $em->getRepository(Hub::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
            return [
                'entity' => $entity,
                'route' => 'hub_show',
                'parameters' => ['token' => $entity->getToken()]
            ];
        elseif ($type === "file"):
            $entity = $em->getRepository(File::class)->findOneBy(['token' => $token], ['updateDate' => 'DESC']);
            return [
                'entity' => $entity,
                'route' => 'file_show',
                'parameters' => ['token' => $entity->getToken()]
            ];
        elseif ($type === "comment"):
            $entity = $em->getRepository(Comment::class)->findOneBy(['id' => $token], ['createDate' => 'DESC']);
            return [
                'entity' => $entity,
                'route' => 'file_show',
                'parameters' => ['token' => $entity->getFile()->getToken()]
            ];
        endif;
    }
}