<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home')]
final class HomeController extends AbstractController
{
    #[Route(name: 'app_home', methods: ['GET'])]
    public function index(
        ItemRepository $itemRepository,
        TournamentRepository $tournamentRepository
    ): Response {

        $latestItems = $itemRepository->findLatestForSale(10);

        // 👇 récupère les tournois de l'utilisateur connecté
        $user = $this->getUser();

        $tournaments = [];

        if ($user) {
            $tournaments = $user->getTournaments(); // ManyToMany OK
        }

        return $this->render('home.html.twig', [
            'latestItems' => $latestItems,
            'tournaments' => $tournaments,
        ]);
    }
}