<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\File;
use App\Entity\Comment;

class CommentController extends AbstractController
{   
    /**
     * @Route("/post/comment/{token}", name="file_comment")
     */
    public function post(Request $request, string $token)
    {
        if ($request->isMethod('POST') && !empty($request->request->get('comment'))) {
            $em = $this->getDoctrine()->getManager();
            $file = $em->getRepository(File::class)->findOneBy(['token' => $token]);
            if ($file) {
                $comment = new Comment();
                $comment->setContent($request->request->get('comment'));
                $comment->setFile($file);
                if ($this->getUser()):
                    $comment->setUser($this->getUser());
                endif;
                $file->setUpdateDate(new \DateTime());
                $file->getStockage()->setUpdateDate(new \DateTime());
                $em->persist($comment);
                $em->flush();
            }
        }
        return $this->redirectToRoute('file_show', ['token' => $token]);
    }
}