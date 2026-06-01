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
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $user = $this->getUser();

         if (!$user) {
           return $this->redirectToRoute('app_login');
         }

        $form = $this->createForm(ShopRequestType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setShopRequest(true);

            $entityManager->flush();

            $this->addFlash('info', 'Une demande a été envoyé. Vous recevrez une confirmation par email');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('shop/request.html.twig', [
            'form' => $form,
        ]);
    }
}