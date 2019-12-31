<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $error = null;
        
        // Check if user already exist. If true return error;
        $em = $this->getDoctrine()->getManager();
        $user_exist = $em->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);
        if ($user_exist) {
            $session = new Session();
            $session->getFlashBag()->add('error', 'User already exist!');
            return $this->redirectToRoute('app');
        }

        if (!$user_exist && $form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->redirectToRoute('app');
            }

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em = $this->getDoctrine()->getManager();
            
            // If first user = admin
            $users = $em->getRepository(User::class)->findAll();
            if (count($users) < 1):
                $roles = ['ROLE_USER', 'ROLE_ADMIN'];
                $user->setRoles($roles);
            else:
                $roles = ['ROLE_USER'];
                $user->setRoles($roles);
            endif;
            
            $em->persist($user);
            $em->flush();

            // User Authentification
            $token = new UsernamePasswordToken($user, null, 'main', $roles);
            $this->get('security.token_storage')->setToken($token);

            return $this->redirectToRoute('app');
        }

        return $this->render('_register.html.twig', [
            'registrationForm' => $form->createView(),
            'error' => $error
        ]);
    }
}
