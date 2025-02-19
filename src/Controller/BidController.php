<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BidController extends AbstractController
{
    #[Route('/bid', name: 'app_bid')]
    public function index(): Response
    {
        return $this->render('bid/index.html.twig', [
            'controller_name' => 'BidController',
        ]);
    }
}
