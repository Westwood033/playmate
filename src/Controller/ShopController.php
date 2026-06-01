<?php

namespace App\Controller;

use App\Form\ShopRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShopController extends AbstractController
{
    #[Route('/shop/request', name: 'app_shop_request')]
    public function request(
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {

        $user = $this->getUser();
        if ($user->hasRole('ROLE_SHOP')) {
            $this->addFlash('warning', 'Vous disposez déjà d\'un compte boutique. Pour le modifier, veuillez contacter le support.');
        }

        $form = $this->createForm(ShopRequestType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setShopRequest(true);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande a bien été envoyée. Vous recevrez une réponse sous peu.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('shop/request.html.twig', [
            'form' => $form,
        ]);
    }
}
