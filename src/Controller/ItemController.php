<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/items')]
class ItemController extends AbstractController
{
    /**
     * Liste avec recherche/filtres via query params :
     *   ?q=mot       → recherche dans name + description
     *   ?category=   → filtre catégorie
     *   ?condition=  → filtre état
     *   ?sold=0|1    → filtre vendu / disponible
     */
    #[Route('', name: 'item_index', methods: ['GET'])]
    public function index(Request $request, ItemRepository $repo): Response
    {
        $filters = [
            'q' => $request->query->get('q', ''),
            'category' => $request->query->get('category', ''),
            'condition' => $request->query->get('condition', ''),
            'sold' => $request->query->get('sold', ''),
        ];

        dump("1");
        $items = $repo->search($filters);
        dump("2");

        return $this->render('item/index.html.twig', [
            'items' => $items,
            'filters' => $filters,
        ]);
    }

    #[Route('/new', name: 'item_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item->setOwner($this->getUser());
            $em->persist($item);
            $em->flush();

            $this->addFlash('success', 'Article publié avec succès !');

            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'item_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Item $item): Response
    {
        return $this->render('item/show.html.twig', [
            'item' => $item,
        ]);
    }

    #[Route('/{id}/edit', name: 'item_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Item $item, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous ne pouvez modifier que vos propres articles.')
            ?: ($item->getOwner() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')
            ? throw $this->createAccessDeniedException()
            : null);

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Article mis à jour.');
            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/new.html.twig', [
            'form' => $form,
            'item' => $item,
            'editing' => true,
        ]);
    }
}
