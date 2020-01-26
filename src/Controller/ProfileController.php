<?php

namespace App\Controller;

use App\Entity\Hub;
use App\Entity\File;
use App\Entity\Like;
use App\Entity\User;
use App\Entity\Comment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile/settings", name="profile_settings")
     */
    public function settings() {
        return $this->render('profile/settings.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
    /**
     * @Route("/profile/api_key", name="profile_api_key")
     */
    public function api_key() {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user->generateApiKey();
        $em->persist($user);
        $em->flush();
        return $this->redirectToRoute('profile_settings');
    }

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

        $files = $em->getRepository(File::class)->findBy(['user' => $user], ['updateDate' => 'DESC']);
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'files' => $files,
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
        
        $hubs = $em->getRepository(Hub::class)->findBy(['user' => $user], ['updateDate' => 'DESC']);

        return $this->render('profile/hubs.html.twig', [
            'user' => $user,
            'hubs' => $hubs,
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
        $files = $em->getRepository(File::class)->findBy(['user' => $user], ['updateDate' => 'DESC']);
        
        return $this->render('profile/files.html.twig', [
            'user' => $user,
            'files' => $files,
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
        
        $likes = $em->getRepository(Like::class)->findBy(['user' => $user], ['createDate' => 'DESC']);
        foreach ($likes as $like) {
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
