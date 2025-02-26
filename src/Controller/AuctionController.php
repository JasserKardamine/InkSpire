<?php

namespace App\Controller;

use App\Entity\Auction;
use App\Form\AuctionType;
use App\Repository\UserRepository;
use App\Repository\ArtworkRepository;
use App\Repository\AuctionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class AuctionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AuctionRepository $auctionRepository;
    private ArtworkRepository $artworkRepository;
    private UserRepository $userRepository;                                                                                   

    public function __construct(EntityManagerInterface $entityManager, AuctionRepository $auctionRepository, ArtworkRepository $artworkRepository, UserRepository $userRepository){
        $this->entityManager = $entityManager;
        $this->auctionRepository = $auctionRepository;
        $this->artworkRepository = $artworkRepository;
        $this->userRepository = $userRepository;
    }
    #[Route('/auction', name: 'app_auction')]
    public function index(): Response
    {
        return $this->render('auction/index.html.twig', [
            'controller_name' => 'AuctionController',
        ]);
    }
    #[Route('/show', name: 'app_auction_show')]
    public function show(Request $request,SessionInterface $session): Response

    {   
        //$session->clear();
        if ($session->has('UserId')) {
            $user = $this->userRepository->find($session->get('UserId', null));
            $filterType = $request->query->get('type', 1);
            if($filterType == 2){
                $auctions = $this->auctionRepository->createQueryBuilder('a')
                ->join('a.artwork', 'artwork')
                ->where('artwork.user = :user')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();
            }
            else {
                $auctions = $this->auctionRepository->findAll();
            }
            return $this->render('auction/show.html.twig', [
                'auctions' => $auctions,
                'filterType' => $filterType,
                'user' => $user,
            ]);
        } 
        else {
            return $this->redirectToRoute('app_signin');
        }
    }
    #[Route('/add', name: 'app_auction_add')]
    public function add(Request $request,SessionInterface $session): Response
    {
        //Session import 
        $user = $this->userRepository->find($session->get('UserId', null));
        //End Session import
        $artworks = $this->artworkRepository->findBy([
            'user' => $user,
            'status' => 0
        ]);
        $auction = new Auction();
        $form = $this->createForm(AuctionType::class, $auction, [
            'artworks' => $artworks,
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                $auction->setEndPrice($auction->getStartPrice());
                $auction->setStatus('open');
                $auction->getArtwork()->setStatus(1);
                $this->entityManager->persist($auction);
                $this->entityManager->flush();
                return $this->redirectToRoute('app_auction_show');
        }
        return $this->render('auction/add.html.twig', [
            'form' => $form->createView(),
            'filterType' => $request->query->getInt('type', 1),
            'user' => $user,
        ]);
    }
    #[Route('/edit/{id}', name: 'app_auction_edit')]
    public function edit(Request $request, int $id,SessionInterface $session): Response
    {
        //Session import 
        $user = $this->userRepository->find($session->get('UserId', null));
        //End Session import
        $auction = $this->auctionRepository->find($id);
        $OldArtwork = $auction->getArtwork();
        $artworks = $this->artworkRepository->createQueryBuilder('artwork')
        ->where('artwork.user = :user')
        ->andWhere('artwork.status = 0')
        ->orWhere(
            'artwork.id = :id AND artwork.status = 1'
        )
        ->setParameter('user', $user)
        ->setParameter('id', $auction->getArtwork()->getId())
        ->getQuery()
        ->getResult();   
        $form = $this->createForm(AuctionType::class, $auction, [
                'artworks' => $artworks,
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $OldArtwork->setStatus(0);
            $auction->getArtwork()->setStatus(1);
            $auction->setEndPrice($auction->getStartPrice());
            $this->entityManager->flush();
            return $this->redirectToRoute('app_auction_show');
        }
        return $this->render('auction/edit.html.twig', [
            'form' => $form->createView(),
            'auction' => $auction,
            'filterType' => $request->query->getInt('type', 1),
            'user' => $user,
        ]);
    }
    #[Route('/delete/{id}', name: 'app_auction_delete')]
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
            return $this->redirectToRoute('app_auction_show');
        }
    }

}
