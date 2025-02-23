<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\User;
use App\Entity\Auction;
use App\Form\SigninType;
use App\Form\AdminAuctionType;
use App\Repository\AuctionRepository;
use App\Repository\ArtworkRepository;
use App\Repository\BidRepository;
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
    private AuctionRepository $auctionRepository;
    private BidRepository $bidRepository;
    private ArtworkRepository $artworkRepository;

    public function __construct(EntityManagerInterface $entityManager , AuctionRepository $auctionRepository , BidRepository $bidRepository , ArtworkRepository $artworkRepository)
     {
        $this->entityManager = $entityManager;
        $this->auctionRepository = $auctionRepository;
        $this->bidRepository = $bidRepository;
        $this->artworkRepository = $artworkRepository;
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

    // Auction (˶˃ᆺ˂˶) ⸜(｡˃ ᵕ ˂ )⸝♡ Ni༼ つ ◕_◕ ༽つgga (˶ᵔ ᵕ ᵔ˶) ദ്ദി(˵ •̀ ᴗ - ˵ ) ✧ (⸝⸝⸝O﹏ O⸝⸝⸝) : 

    #[Route('/admin/auction/show', name: 'app_admin_auction_show')]
    public function show(Request $request): Response
    {   
        $auctions = $this->auctionRepository->findAll();    
        return $this->render('admin/auction/show.html.twig', [
            'auctions' => $auctions,
        ]);
    }
    #[Route('/admin/auction/add/', name: 'app_admin_auction_add')]
    public function add(Request $request): Response
    {
        $auction = new Auction();
        $artworks = $this->artworkRepository->findBy([
            'status' => 0,
        ]);
        $form = $this->createForm(AdminAuctionType::class, $auction,[
            'artworks' => $artworks,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $auction->setStatus("ongoing");
            $auction->getArtwork()->setStatus(1);
            $auction->setEndPrice($auction->getStartPrice());
            $this->entityManager->persist($auction);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_admin_auction_show');
        }
        return $this->render('admin/auction/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/auction/edit/{id}', name: 'app_admin_auction_edit')]
    public function edit(Request $request, int $id): Response
    {
        $auction = $this->auctionRepository->find($id);
        $OldArtwork = $auction->getArtwork();
        if(!$auction){
            throw $this->createNotFoundException('auction not found');
        }
        else {
            $artworks = $this->artworkRepository->createQueryBuilder('artwork')
            ->where('artwork.status = 0')
            ->orWhere('artwork.id = :id')
            ->setParameter('id', $auction->getArtwork()->getId())
            ->getQuery()
            ->getResult();

            $form = $this->createForm(AdminAuctionType::class, $auction,[
               'artworks' => $artworks,   
            ]);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $OldArtwork->setStatus(0);
                $auction->getArtwork()->setStatus(1);
                $auction->setEndPrice($auction->getStartPrice());
                $this->entityManager->flush();
                return $this->redirectToRoute('app_admin_auction_show');
            }
            return $this->render('admin/auction/edit.html.twig', [
                'form' => $form->createView(),
                'auction' => $auction,
            ]);
        }
    }
    #[Route('/admin/auction/delete/{id}', name: 'app_admin_auction_delete')]
    public function delete(int $id): Response
    {
        $auction = $this->auctionRepository->find($id);
        if(!$auction){
            throw $this->createNotFoundException('auction not found');
        }
        else{
            $auction->getArtwork()->setStatus(0);
            $this->entityManager->remove($auction);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_admin_auction_show');
        }
    }
    #[Route('/admin/auction/bid/show/{id}', name: 'app_admin_auction_show_bid')]
    public function showBid(int $id): Response
    {
        $bids = $this->bidRepository->findBy(['auction' => $id]);

        return $this->render('admin/bid/show.html.twig', [
            'bids' => $bids,
            'message' => empty($bids) ? "No bids found." : null,
        ]);
    }
    // end auction ----------------
}
