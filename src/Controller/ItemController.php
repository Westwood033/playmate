<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/items')]
class ItemController extends AbstractController
{
    public function __construct(
        private readonly SluggerInterface $slugger,
    )
    {
    }

    #[Route('', name: 'item_index', methods: ['GET'])]
    public function index(Request $request, ItemRepository $repo): Response
    {
        $filters = [
            'q' => $request->query->get('q', ''),
            'category' => $request->query->get('category', ''),
            'condition' => $request->query->get('condition', ''),
            'sold' => $request->query->get('sold', ''),
        ];

        return $this->render('item/index.html.twig', [
            'items' => $repo->search($filters),
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
            $item->setImages($this->handleImageUploads($form->get('imageFiles')->getData()));
            $item->setOwner($this->getUser());
            $em->persist($item);
            $em->flush();

            $this->addFlash('success', 'Article publié avec succès !');
            return $this->redirectToRoute('item_index');
        }

        return $this->render('item/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'item_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Item $item): Response
    {
        return $this->render('item/show.html.twig', ['item' => $item]);
    }

    #[Route('/{id}/edit', name: 'item_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Item $item, EntityManagerInterface $em): Response
    {
        if ($item->getOwner() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres articles.');
        }

        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Ordre des images existantes transmis par le JS
            $existingOrder = json_decode(
                $request->request->get('existing_images_order', '[]'),
                true
            ) ?? [];

            // 2. Supprimer du disque les images retirées
            $uploadDir = $this->getParameter('items_images_directory');
            foreach (array_diff($item->getImages(), $existingOrder) as $removed) {
                $path = $uploadDir . '/' . $removed;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            // 3. Uploader les nouvelles images
            $newPaths = $this->handleImageUploads($form->get('imageFiles')->getData());

            // 4. Fusionner : existantes (ordre utilisateur) + nouvelles
            $item->setImages(array_values(array_merge($existingOrder, $newPaths)));

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

    /**
     * @param UploadedFile[]|null $uploadedFiles
     * @return string[]
     */
    private function handleImageUploads(?array $uploadedFiles): array
    {
        if (empty($uploadedFiles)) {
            return [];
        }

        $uploadDir = $this->getParameter('items_images_directory');
        $paths = [];

        foreach ($uploadedFiles as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $safe = $this->slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $filename = $safe . '-' . uniqid() . '.' . $file->guessExtension();

            $file->move($uploadDir, $filename);
            $paths[] = $filename;
        }

        return $paths;
    }
}
