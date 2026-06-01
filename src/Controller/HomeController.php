<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home')]
final class HomeController extends AbstractController
{
    #[Route(name: 'app_home', methods: ['GET'])]
    public function index(ItemRepository $itemRepository): Response
    {
        $latestItems = $itemRepository->findLatestForSale(10);

        return $this->render('home.html.twig', [
            'latestItems' => $latestItems,
        ]);
    }
}