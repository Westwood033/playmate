<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly SluggerInterface $slugger,
    ) {}

    #[Route(name: 'app_profile', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $avatarFile */
            $avatarFile = $form->get('avatarFile')->getData();

            if ($avatarFile instanceof UploadedFile) {
                $uploadDir = $this->getParameter('avatars_directory');
                $safe = $this->slugger->slug(pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME));

                try {
                    $extension = $avatarFile->guessExtension();
                } catch (\Throwable) {
                    $extension = null;
                }

                $extension = $extension ?: $avatarFile->getClientOriginalExtension() ?: 'jpg';
                $filename = $safe . '-' . uniqid() . '.' . strtolower($extension);

                $avatarFile->move($uploadDir, $filename);

                if ($user->getAvatar()) {
                    $oldAvatarPath = $uploadDir . '/' . $user->getAvatar();
                    if (is_file($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                $user->setAvatar($filename);
            }

            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }
}
