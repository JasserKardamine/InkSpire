<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
     {
         $this->entityManager = $entityManager;
     }
     
    #[Route('/home', name: 'app_home')]
    public function index(SessionInterface $session): Response
    {
        $userid = $session->get('UserId',null) ; 

        if(!$userid){
            return $this->render('home/index.html.twig',[
                'user' => null ,  
            ]);
        }
   
        $user = $this->entityManager->getRepository(User::class)->find($userid);
    
        return $this->render('home/index.html.twig', [
            'user' => $user , 
        ]);
    }



}
