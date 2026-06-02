<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/transactions')]
final class TransactionController extends AbstractController
{
    #[Route('', name: 'transaction_index')]
    public function index(): Response
    {
        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
        ]);
    }

    #[Route('/new/{id}', name: 'transaction_new', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em, Item $item): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User */
            $user = $this->getUser();
            $transaction->setBuyer($user);
            $transaction->setTransactedAt(new DateTimeImmutable());
            $transaction->setItem($item);
            $transaction->setSeller($item->getOwner());
            $transaction->setSellerAddress($item->getOwner()->getAddress());
            $item->setIsSold(true);
            $user->addItem($item);
            $em->persist($transaction);
            $em->flush();

            return $this->redirectToRoute('item_show', ["id" => $item->getId()]);
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form,
            'item' => $item,
        ]);
    }

    #[Route('/history/{id}', name: 'transaction_history', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function history(TransactionRepository $repo): Response
    {
        return $this->render('transaction/history/index.html.twig', [
            'transactions' => $repo->findAll(),
        ]);
    }
}
