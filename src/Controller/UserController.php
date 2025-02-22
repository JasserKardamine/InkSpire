<?php

namespace App\Controller;

use App\Entity\User ;
use App\Form\ChangepasswordType;
use App\Form\EditType;
use App\Form\SigninType;
use App\Form\SignupType;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
     {
         $this->entityManager = $entityManager;
     }


     public function sendEmail(MailerInterface $mailer, string $destination, string $content): bool 
     {
         try {
             $email = (new Email())
                 ->from('support@eInkSpire.com')
                 ->to($destination)
                 ->priority(Email::PRIORITY_HIGH)
                 ->subject('Verify your account!')
                 ->text($content);
                 
     
             $mailer->send($email); 
             return true; 
     
         } catch (\Exception $e) {
             return false; 
         }
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
    public function UserSignup(Request $request , UserPasswordHasherInterface $passwordHasher , SessionInterface $session,MailerInterface $mailer) {
        $user = new User() ; 
        $SignupForm = $this->createForm(SignupType::class,$user) ; 
        $SignupForm->handleRequest($request) ; 

        if($SignupForm->isSubmitted() && $SignupForm->isValid()) { 
            
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setTokens(10);
            $user->setRole(0) ; 

            $verificationCode = rand(100000, 999999); 
            $session->set('verification_code', $verificationCode);
            $session->set('temp_user', serialize($user));
          

            if ( $this->sendEmail($mailer,$user->getEmail(),$verificationCode) ) {
                return $this->redirectToRoute('app_verify');
            } else{
                return $this->redirectToRoute('app_signin');
            }
           
        }
        return $this->render('user/signup.html.twig', [
            'form' => $SignupForm->createView() , 
        ]);
    }



    #[Route('/verify', name: 'app_verify')]
    public function verifyCode(Request $request, SessionInterface $session): Response
    {
        $user = unserialize($session->get('temp_user')); 
        $correctCode = $session->get('verification_code');
        
        if (!$user || !$correctCode) {
            return $this->redirectToRoute('app_signup'); 
        }

        if ($request->isMethod('POST')) {
            $piece1 = $request->request->get('1');
            $piece2 = $request->request->get('2');
            $piece3 = $request->request->get('3');
            $piece4 = $request->request->get('4');
            $piece5 = $request->request->get('5');
            $piece6 = $request->request->get('6');

            $enteredCode = $piece1 . $piece2 . $piece3 . $piece4 . $piece5 . $piece6 ; 

            if ($enteredCode == $correctCode) {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                // Clear session data
                $session->remove('verification_code');
                $session->remove('temp_user');

                return $this->redirectToRoute('app_signin'); 
            } 
        }

        return $this->render('user/verify.html.twig',[
            'user' => $user
        ]);
    }



    #[Route('/logout' , name : 'app_logout')]
    public function UserLogout(SessionInterface $session): Response
    {   
        $session->invalidate(); 
        return $this->redirectToRoute('app_signin');
    }



    #[Route('/Profile' , name : 'app_profile')]
    public function UserProfile(SessionInterface $session) : Response
    {   
        $userid = $session->get('UserId');
    
        if (!$userid || !($user = $this->entityManager->getRepository(User::class)->find($userid))) {
            return $this->redirectToRoute('app_signin');
        }

        if($user) {
            return $this->render('user/profile.html.twig',[
               'user' => $user
            ]);
        
        }else{
            return $this->redirectToRoute('app_home');
        }
    }



    #[Route('/edit', name: 'app_edit')]
    public function UserEdit(SessionInterface $session, Request $request): Response
    {
       
        $userid = $session->get('UserId');
    
        if (!$userid || !($user = $this->entityManager->getRepository(User::class)->find($userid))) {
            return $this->redirectToRoute('app_signin');
        }
    
        
        $EditForm = $this->createForm(EditType::class, $user);
        $EditForm->handleRequest($request);
    
        if ($EditForm->isSubmitted() && $EditForm->isValid()) {
           
            $file = $EditForm->get('picture')->getData();
            if ($file) {
                try {
                    $uploadsDirectory = $this->getParameter('uploads_directory');
                    $newFilename = uniqid().'.'.$file->guessExtension();
                    $file->move($uploadsDirectory, $newFilename);
                    $user->setPicture($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'File upload failed. Please try again.');
                    return $this->redirectToRoute('app_edit');
                }
            }
    
            $this->entityManager->flush(); 
            return $this->redirectToRoute('app_profile');
        }
    

        return $this->render('user/edit.html.twig', [
            'form' => $EditForm->createView(),
            'user' => $user
        ]);
    }
    


    #[Route('/canelacc' , name : 'app_cancelacc')]
    public function CacelAccount(SessionInterface $session) {
        $userid = $session->get("UserId",null) ; 
        $user = $this->entityManager->getRepository(User::class)->find($userid); 

        if($user != null){
            $this->entityManager->remove($user) ; 
            $this->entityManager->flush() ; 
        }
        $session->clear() ; 
        return $this->redirectToRoute('app_home');
    }  


    #[Route('/changepass', name: "app_change_password")]
    public function ChangePassword(SessionInterface $session, Request $request, UserPasswordHasherInterface $passwordHasher) {
        $userid = $session->get("UserId", null);
        $user = $this->entityManager->getRepository(User::class)->find($userid);
    
        if (!$user) {
            return $this->redirectToRoute('app_signin');
        }
    
        $ChangePasswordForm = $this->createForm(ChangepasswordType::class, null);
        $ChangePasswordForm->handleRequest($request);
    
        if ($ChangePasswordForm->isSubmitted() && $ChangePasswordForm->isValid()) {
            $formData = $ChangePasswordForm->getData();
    
            $currentpassword = $formData['currentpassword'];
            $newpassword = $formData['newpassword'];
            $confirmation = $formData['confirmpassword'];
    
            if ($passwordHasher->isPasswordValid($user, $currentpassword)) {
                if ($newpassword === $confirmation) {
                    $hashedPassword = $passwordHasher->hashPassword($user, $newpassword);
                    $user->setPassword($hashedPassword);
    
                    $this->entityManager->flush();
    
                    return $this->redirectToRoute('app_edit');
                }
            }
        }
    
        return $this->render('user/changepassword.html.twig', [
            'form' => $ChangePasswordForm->createView(),
            'user' => $user
        ]);
    }
    

}
