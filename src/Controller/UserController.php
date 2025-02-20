<?php

namespace App\Controller;

use App\Entity\User ;
use App\Form\SigninType;
use App\Form\SignupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
     {
         $this->entityManager = $entityManager;
     }

    #[Route('/signin', name: 'app_signin')]
    public function SignIn(Request $request,SessionInterface $session,UserPasswordHasherInterface $passwordHasher): Response
    {
        $SigninForm = $this->createForm(SigninType::class) ;
        $SigninForm->handleRequest($request) ;
        
        if($SigninForm->isSubmitted() && $SigninForm->isValid()) {

            $email = $SigninForm->get('email')->getData() ; 
            $password = trim($SigninForm->get('password')->getData()) ;  

            $SessionUser = $this->entityManager->getRepository(User::class)->findOneBy(['email'=> $email])  ;  

            if($SessionUser){
                if($passwordHasher->isPasswordValid($SessionUser,$password)) {
                    $session->set('UserId',$SessionUser->getId()) ; 
                    return $this->redirectToRoute('app_home');
                }
            }
        }
        return $this->render('user/signin.html.twig', [
            'form' => $SigninForm,
        ]);
    }



    #[Route('/signup' , name: 'app_signup')]
    public function UserSignup(Request $request , UserPasswordHasherInterface $passwordHasher) {
        $user = new User() ; 
        $SignupForm = $this->createForm(SignupType::class,$user) ; 
        $SignupForm->handleRequest($request) ; 

        if($SignupForm->isSubmitted() && $SignupForm->isValid()) { 
            
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user) ;
            $this->entityManager->flush() ;
            return $this->redirectToRoute('app_home');
        }
        return $this->render('user/signup.html.twig', [
            'form' => $SignupForm , 
        ]);
    }
}
