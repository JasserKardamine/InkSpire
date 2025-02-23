<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SigninType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
     {
         $this->entityManager = $entityManager;
     }

     // user type shit : 

    #[Route('/admin', name: 'app_loginadmin')]
    public function AdminLogin(Request $request, SessionInterface $session, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userid = $session->get('UserId', null);

        if ($userid) {
            $user = $this->entityManager->getRepository(User::class)->find($userid);
            if ($user && $user->getRole() === 1) {
                return $this->redirectToRoute('app_dashboard');
            }
        }
    
        $form = $this->createForm(SigninType::class);
        $form->handleRequest($request);
    
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('admin/AdminSignin.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    
        $email = $form->get('email')->getData();
        $password = trim($form->get('password')->getData());
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash('error', 'Invalid email or password.');
            return $this->redirectToRoute('app_loginadmin');
        }
    
        if ($user->getRole() === 1) {
            $session->set('UserId', $user->getId());
            return $this->redirectToRoute('app_dashboard');    
        }
    
        return $this->render('admin/AdminSignin.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

     


    #[Route('/dashboard', name: 'app_dashboard')]
    public function DisplayUsers(SessionInterface $session): Response
    {
        $userid = $session->get("UserId" , null ) ; 
        if(!$userid){
           return $this->redirectToRoute('app_home');
        }

        $SessionUser = $this->entityManager->getRepository(User::class)->find($userid) ; 
        if($SessionUser && $SessionUser->getRole() == 0 ) {
            return $this->redirectToRoute('app_home');
        }

        $users = $this->entityManager->getRepository(User::class)->findAll() ; 
        return $this->render('admin/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/banuser/{id}' , name:'app_ban')]
    public function BanUser(int $id ,SessionInterface $session): Response {     

        $userid = $session->get("UserId" , null ) ; 
        if(!$userid){
           return $this->redirectToRoute('app_home');
        }

        $SessionUser = $this->entityManager->getRepository(User::class)->find($userid) ; 

        if($SessionUser && $SessionUser->getRole() == 0 ) {
            return $this->redirectToRoute('app_home');
        }

        $User = $this->entityManager->getRepository(User::class)->find($id) ; 
        $User->setStatus(0) ; 
        $this->entityManager->flush() ; 
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/Unban/{id}' , name:'app_unban')]
    public function UnbanUser(int $id , SessionInterface $session): Response { 

         $userid = $session->get("UserId" , null ) ; 
        if(!$userid){
           return $this->redirectToRoute('app_home');
        }

        $SessionUser = $this->entityManager->getRepository(User::class)->find($userid) ; 
        if($SessionUser && $SessionUser->getRole() == 0 ) {
            return $this->redirectToRoute('app_home');
        }

        $User = $this->entityManager->getRepository(User::class)->find($id) ; 
        $User->setStatus(1) ; 
        $this->entityManager->flush() ; 
        return $this->redirectToRoute('app_dashboard');
    }

    //end user ----------------
}
