<?php

namespace App\Controller;

use App\Entity\Hub;
use App\Entity\File;
use App\Entity\User;
use App\Entity\Comment;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile/{token}", name="profile")
     */
    public function index(?string $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($token):
            $user = $em->getRepository(User::class)->findOneBy(['token' => $token]);
        else:
            $user = $this->getUser();
        endif;
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/profile/{token}/hubs", name="profile_hubs")
     */
    public function hubs(?string $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($token):
            $user = $em->getRepository(User::class)->findOneBy(['token' => $token]);
        else:
            $user = $this->getUser();
        endif;
        
        return $this->render('profile/hubs.html.twig', [
            'user' => $user,
            'hubs' => $user->getHubs(),
        ]);
    }

    /**
     * @Route("/profile/{token}/files", name="profile_files")
     */
    public function files(?string $token = null)
    {
        $em = $this->getDoctrine()->getManager();
        if ($token):
            $user = $em->getRepository(User::class)->findOneBy(['token' => $token]);
        else:
            $user = $this->getUser();
        endif;
        
        return $this->render('profile/files.html.twig', [
            'user' => $user,
            'files' => $user->getFiles(),
        ]);
    }

    /**
     * @Route("/profile/{token}/likes/{type}", name="profile_likes")
     */
    public function likes(?string $token = null, string $type)
    {
        $em = $this->getDoctrine()->getManager();
        if ($token):
            $user = $em->getRepository(User::class)->findOneBy(['token' => $token]);
        else:
            $user = $this->getUser();
        endif;
        $hubs = [];
        $files = [];
        $comments = [];
        foreach ($user->getLikes() as $like) {
            if ($type === "hub" && $like->getHub()):
                $hub = $em->getRepository(Hub::class)->findOneBy(['id' => $like->getHub()->getId()]);
                if ($hub):
                    $hubs[] = $hub;
                endif;
            elseif ($type === "file" && $like->getFile()):
                $file = $em->getRepository(File::class)->findOneBy(['id' => $like->getFile()->getId()]);
                if ($file):
                    $files[] = $file;
                endif;
            elseif ($type === "comment" && $like->getComment()):
                $comment = $em->getRepository(Comment::class)->findOneBy(['id' => $like->getComment()->getId()]);
                if ($comment):
                    $comments[] = $comment;
                endif;
            endif;
        }
        return $this->render('profile/likes.html.twig', [
            'user' => $user,
            'hubs' => $hubs,
            'files' => $files,
            'comments' => $comments,
        ]);
    }
}
