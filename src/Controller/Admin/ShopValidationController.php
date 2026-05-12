<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopValidationController extends AbstractController
{
    #[Route('/admin/shop/validate/{id}', name: 'admin_shop_validate')]
    public function validate(User $user, EntityManagerInterface $em): Response
    {
        // sécurité : seul admin peut valider
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // vérifier qu’il a bien fait une demande boutique
       /* if (!$user->isShopRequest()) {
            $this->addFlash('warning', 'Cet utilisateur n’a pas fait de demande boutique.');
            return $this->redirectToRoute('app_shop_request');
        }*/

        // attribution du rôle boutique
        $user->addRole('ROLE_SHOP');

        // on retire la demande
        $user->setShopRequest(false);

        // sauvegarde
        $em->flush();

        $this->addFlash('success', 'Boutique validée avec succès.');

        return $this->redirectToRoute('app_shop_request');
    }

    #[Route('/admin/shop/reject/{id}', name: 'admin_shop_reject')]
    public function reject(User $user, EntityManagerInterface $em): Response
    {
        // sécurité admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on refuse la demande
        $user->setShopRequest(false);

        $em->flush();

        $this->addFlash('success', 'Demande boutique refusée.');

        return $this->redirectToRoute('app_shop_request');
    }
    #[Route('/admin/shop/requests', name: 'admin_shop_requests')]
public function requests(UserRepository $repo): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    $users = $repo->findBy(['shopRequest' => true]);

    return $this->render('admin/shop_requests.html.twig', [
        'users' => $users
    ]);
}
}