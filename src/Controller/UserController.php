<?php

namespace App\Controller;

use App\Entity\User ;
use App\Form\EditType;
use App\Form\SigninType;
use App\Form\SignupType;
use App\Form\GooglesignupType;
use App\Form\ResetrequestType;
use App\Form\ResetpasswordType;
use App\Form\ChangepasswordType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private TokenStorageInterface $tokenStorage;
    private EventDispatcherInterface $eventDispatcher;
    private HttpClientInterface $httpClient;


    public function __construct(
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher,
    TokenStorageInterface $tokenStorage,
    EventDispatcherInterface $eventDispatcher,
    HttpClientInterface $httpClient
    )
     {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->httpClient = $httpClient;
     }


    // access function (tab3a admin ) 
    private function redirectIfUser(SessionInterface $session): ?Response
    {
        $userid = $session->get('UserId');
        if (!$userid) {
            return $this->redirectToRoute('app_signin'); 
        }

        $user = $this->entityManager->getRepository(User::class)->find($userid);

        if ($user && $user->getRole() === 1) { 
            return $this->redirectToRoute('app_loginadmin');
        }
        return null;
    }


    public function sendEmail(MailerInterface $mailer, string $destination, string $content): string 
    {
        try {
            $email = (new Email())
                ->from('support@eInkSpire.com')
                ->to($destination)
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Verify your account!')
                ->text($content);
    
            $mailer->send($email); 
            return "Email sent successfully."; 
    
        } catch (\Exception $e) {
            return "Email sending failed: " . $e->getMessage();
        }
    }

    private function authenticate(
        $user,
        SessionInterface $session,
        Request $request) {

         //setting up authentification (badelt l auth handler )
         $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
         $this->tokenStorage->setToken($token);
         $this->eventDispatcher->dispatch(new InteractiveLoginEvent($request, $token));
         $session->set('UserId', $user->getId());

    }
    
    private function GoogleSignIn($userData,$session,$request) : Response {
        $email = $userData['email'];
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if($user) {
           $this->authenticate($user,$session,$request) ; 
           return $this->redirectToRoute('app_home');
        }
       return $this->redirectToRoute('app_signup');
    } 



    #[Route('/user/google/signup' , name : 'google_signup_app')]
    public function GoogleSignUp( Request $request , SessionInterface $session) : Response{

        $userData = $session->get("google_user_data",null) ; 

        $user = new User();
        $user->setEmail($userData['email']);
        $user->setfirstName($userData['given_name'] ?? '');
        $user->setlastName($userData['family_name'] ?? '');
        $user->setRole(0) ; 
        $user->setStatus(1) ; 
        $user->setTokens(10) ;

        if($this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']])){
            return $this->redirectToRoute('app_signin');
        }

        $form = $this->createForm(GooglesignupType::class) ; 
        $form->handleRequest($request) ; 

        if($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $password = $formData['password'] ; 
            $confirmpassword =  $formData['confirmpassword'];

            if($password === $confirmpassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user,$password);
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_signin');
            }  
        }
        return $this->render('user/googlesignup.html.twig',[
            'form'=>$form->createView() , 
        ]);
    }


     
     #[Route('/user/signin', name: 'app_signin')]
     public function SignIn(Request $request , SessionInterface $session): Response
     { 
         $form = $this->createForm(SigninType::class);
         $form->handleRequest($request);
        
         if($session->has("UserId")) {
            $userid = $session->get("UserId", null ) ; 
            if ($this->entityManager->getRepository(User::class)->find($userid)->getRole() == 0) {
            return $this->redirectToRoute('app_home');
            }
         }
        
         //entering state 
         if (!$form->isSubmitted() || !$form->isValid()) {
             return $this->render('user/signin.html.twig', ['form' => $form->createView()]);
         }

     
         $email = $form->get('email')->getData();
         $password = trim($form->get('password')->getData());
         $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
     
         if (!$user || !$this->passwordHasher->isPasswordValid($user, $password) || $user->getStatus() != 1 ) {
             return $this->render('user/signin.html.twig', ['form' => $form->createView()]);
         }
      
         $this->authenticate($user,$session,$request) ; 
            
        return $this->redirectToRoute('app_home');
     }
     



    #[Route('/user/signup' , name: 'app_signup')]
    public function UserSignup(
        Request $request,
        SessionInterface $session,
        MailerInterface $mailer ) {

        $user = new User() ; 
        $SignupForm = $this->createForm(SignupType::class,$user) ; 
        $SignupForm->handleRequest($request) ; 
        
       
        if($session->has("UserId")) {
            $userid = $session->get("UserId", null ) ; 
            if ($this->entityManager->getRepository(User::class)->find($userid)->getRole() == 0) {
            return $this->redirectToRoute('app_home');
            }
         }
        
        if($SignupForm->isSubmitted() && $SignupForm->isValid()) { 
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setTokens(10);
            $user->setRole(0) ; 
            $user->setStatus(1) ; 

            $verificationCode = rand(100000, 999999); 
            $session->set('verification_code', $verificationCode);
            $session->set('temp_user', serialize($user));
            $session->set('verification_action','signup') ; 
          

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

/* Verification : */

    #[Route('/user/verify', name: 'app_verify')]
    public function verifyCode(Request $request, SessionInterface $session): Response
    {
        $user = unserialize($session->get('temp_user'));
        $correctCode = $session->get('verification_code');
        $action = $session->get('verification_action'); 

        
        if (!$user || !$correctCode) {
            return $this->redirectToRoute('app_signup');
        }

        if ($request->isMethod('POST')  && $request->request->get('form_identifier') === 'verification_form') {
            
            $enteredCode = $this->getEnteredCode($request);

            if ($enteredCode != $correctCode) { 
                return $this->render('user/verify.html.twig', ['user' => $user]);
            }
           
            if ($action === "signup") {
                return $this->processSignup($session, $user);
            }
            
            return $this->redirectToRoute('app_reset_password'); 
        }

        return $this->render('user/verify.html.twig', ['user' => $user]);
    }

   
    private function getEnteredCode(Request $request): string
    {
        $code = '';
        for ($i = 1; $i <= 6; $i++) {
            $code .= $request->request->get((string)$i, '');
        }
        return $code;
    }

   
    private function processSignup(SessionInterface $session, $user): Response
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->clearSession($session);
        return $this->redirectToRoute('app_signin');
    }


    private function clearSession(SessionInterface $session): void
    {
        $session->remove('verification_code');
        $session->remove('temp_user');
        $session->remove('verification_action');
    }

/* l 9bal lkoll tab3in l verification */ 



    #[Route('/user/logout' , name : 'app_logout')]
    public function UserLogout(SessionInterface $session): Response
    {   
        $session->invalidate(); 
        return $this->redirectToRoute('app_signin');
    }



    #[Route('/user/Profile' , name : 'app_profile')]
    public function UserProfile() : Response
    {   

        $user = $this->getUser();
    
        if (!$user) {
            return $this->redirectToRoute('app_signin');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }



    #[Route('/user/edit', name: 'app_edit')]
    public function UserEdit(SessionInterface $session, Request $request): Response
    {
       
        if ($redirect = $this->redirectIfUser($session)) {
            return $redirect;
        }

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
    


    #[Route('/user/canelacc' , name : 'app_cancelacc')]
    public function CacelAccount(SessionInterface $session) {

        $user = $this->getUser();

        if($user){
            $this->entityManager->remove($user) ; 
            $this->entityManager->flush() ; 
            $this->container->get('security.token_storage')->setToken(null);
        }
        $session->clear() ; 
        return $this->redirectToRoute('app_home');
    }  



    #[Route('/user/changepass', name: "app_change_password")]
    public function ChangePassword(SessionInterface $session, Request $request) {

        if ($redirect = $this->redirectIfUser($session)) {
            return $redirect;
        }

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
    
            if ($this->passwordHasher->isPasswordValid($user, $currentpassword)) {
                if ($newpassword === $confirmation) {
                    $hashedPassword = $this->passwordHasher->hashPassword($user, $newpassword);
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
    



    #[Route('user/signin/google', name: 'google_signin')]
    public function googleLogin(SessionInterface $session, Request $request ): RedirectResponse
    {
        if($session->has("UserId")) {
            return $this->redirectToRoute('app_home');
        }


        $clientId = $_ENV['GOOGLE_CLIENT_ID'];
        $redirectUri = $_ENV['GOOGLE_REDIRECT_URI'];
        $scope = urlencode("email profile");
    
        $url = "https://accounts.google.com/o/oauth2/auth?response_type=code&client_id={$clientId}&redirect_uri={$redirectUri}&scope={$scope}&prompt=select_account";
    
       

        // sending the action to the handler : 
        $action = $request->query->get('action', 'signin');
        $session->set("action" , $action) ; 
        
        return new RedirectResponse($url);
    }

    
    #[Route('/user/signin/google/callback', name: 'google_callback')]
    public function googleCallback(Request $request , SessionInterface $session): Response
    {
        if ($session->has('UserId')) {
            return $this->redirectToRoute('app_home');
        }
        
        $code = $request->query->get('code');

        if (!$code) {
            return new Response('Authorization code not found.', Response::HTTP_BAD_REQUEST);
        }

        // Exchange code for access token
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
            'body' => [
                'client_id' => $_ENV['GOOGLE_CLIENT_ID'],
                'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'],
                'redirect_uri' => $_ENV['GOOGLE_REDIRECT_URI'],
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]
        ]);

        $data = $response->toArray();

        if (!isset($data['access_token'])) {
            return new Response('Access token not found.', Response::HTTP_BAD_REQUEST);
        }

        // Fetch user data from Google
        $userResponse = $httpClient->request('GET', 'https://www.googleapis.com/oauth2/v1/userinfo', [
            'headers' => ['Authorization' => 'Bearer ' . $data['access_token']],
        ]);

        $userData = $userResponse->toArray();
        
        //retriving the action : 
        $action = $session->get("action",null) ;  
        $session->remove("action") ; 
        
        if ($action == "signin") {
        
        return $this->GoogleSignIn($userData,$session,$request) ; 
        }
        else{
            $session->set('google_user_data', $userData);
            return $this->redirectToRoute('google_signup_app');
        }
    
        
    }
    


}

