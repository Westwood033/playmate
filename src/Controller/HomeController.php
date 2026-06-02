<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TournamentRepository;
use Datetime;

#[Route('/home')]
final class HomeController extends AbstractController
{
    #[Route(name: 'app_home', methods: ['GET'])]
    public function index(ItemRepository $itemRepository, TournamentRepository $tournamentRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $latestItems = $itemRepository->findLatestForSale(10);

        return $this->render('home.html.twig', [
            'latestItems' => $latestItems,
            'tournaments' => $tournamentRepository->findAllInWeek(new Datetime),
        ]);
    }
}
