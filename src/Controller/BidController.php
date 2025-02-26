<?php

namespace App\Controller;

use App\Entity\Bid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\AuctionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Repository\BidRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class BidController extends AbstractController
{   
    private EntityManagerInterface $entityManager;
    private AuctionRepository $auctionRepository;
    private UserRepository $userRepository;
    private BidRepository $bidRepository;
    public function __construct(EntityManagerInterface $entityManager, AuctionRepository $auctionRepository, UserRepository $userRepository, BidRepository $bidRepository){
        $this->entityManager = $entityManager;
        $this->auctionRepository = $auctionRepository;
        $this->userRepository = $userRepository;
        $this->bidRepository = $bidRepository;
    }
    #[Route('/bid', name: 'app_bid')]
    public function index(): Response
    {
        return $this->render('bid/index.html.twig', [
            'controller_name' => 'BidController',
        ]);
    }
    #[Route('/bid/show', name: 'app_bid_show')]
    public function show(Request $request,SessionInterface $session): Response
    {   
        //Session import 
        $user = $this->userRepository->find($session->get('UserId', null));
        //End Session import
        $bids = $this->bidRepository->findBy(['user' => $user]);
        return $this->render('bid/show.html.twig', [
            'bids' => $bids,
            'user' => $user,
        ]);
    }
    #[Route('/bid/add', name: 'app_bid_add')]
    public function add(Request $request,SessionInterface $session): Response
    {   
        //Session import 
        $user = $this->userRepository->find($session->get('UserId', null));
        //End Session import
        $bidAmount = $request->request->get('bidAmount');
        $auction = $this->auctionRepository->find($request->request->get('auctionId'));
        if ($bidAmount && $auction) {
            $bid = new Bid();
            $currentDateTime = new \DateTime();
            $bid->setAmount($bidAmount);
            if($auction->getEndPrice() < $bidAmount){
                $auction->setEndPrice($bidAmount);
            }
            $bid->setAuction($auction);
            $bid->setUser($user);
            $bid->setTime($currentDateTime);
            $this->entityManager->persist($bid);
            $this->entityManager->flush();
            $this->addFlash('success', 'bid added successfully');
            return $this->redirectToRoute('app_auction_show');
        }
        else{
            $this->addFlash('error', 'bid not added');
        }
        return $this->render('auction/show.html.twig', [
            'filterType' => $request->query->getInt('type', 1),
        ]);
    }
    #[Route('/bid/delete/{id}', name: 'app_bid_delete')]
    public function delete(int $id): Response
    {
        $bid = $this->bidRepository->find($id);
        if(!$bid){
            throw $this->createNotFoundException('bid not found');
        }
        else{
            $this->entityManager->remove($bid);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_bid_show');
        }
    }
}
