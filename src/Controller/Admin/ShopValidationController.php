<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ShopValidationController extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/admin/shop/validate/{id}', name: 'admin_shop_validate')]
    public function validate(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$user->isShopRequest()) {
             $this->addFlash('warning', 'Cet utilisateur n\'a pas fait de demande boutique.');
             return $this->redirectToRoute('app_shop_request');
         }

        if ($user->hasRole('ROLE_SHOP')) {
            $this->addFlash('warning', 'Cet utilisateur a déjà une boutique associée.');
        }

        $user->addRole('ROLE_SHOP');
        $user->setShopRequest(false);

        $em->flush();

        $email = (new TemplatedEmail())
            ->from("noreply@playmate.fr")
            ->to($user->getEmail())
            ->subject("Demande de boutique acceptée")
            ->htmlTemplate('shop/email/accept.html.twig')
            ->context([
                'shopName' => $user->getShopName(),
                'shopAddress' => $user->getShopAddress(),
                'shopPhone' => $user->getPhone(),
            ]);

        $this->mailer->send($email);

        $this->addFlash('success', 'Boutique validée avec succès.');

        return $this->redirectToRoute('admin_shop_request_index');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/admin/shop/reject/{id}', name: 'admin_shop_reject')]
    public function reject(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$user->isShopRequest()) {
            $this->addFlash('warning', 'Cet utilisateur n\'a pas fait de demande boutique.');
        }
        $user->setShopRequest(false);

        $em->flush();

        $email = (new TemplatedEmail())
            ->from("noreply@playmate.fr")
            ->to($user->getEmail())
            ->subject("Demande de boutique refusée")
            ->htmlTemplate('shop/email/reject.html.twig')
            ->context([
                'shopName' => $user->getShopName(),
                'shopAddress' => $user->getShopAddress(),
                'shopPhone' => $user->getPhone(),
            ]);

        $this->mailer->send($email);

        $this->addFlash('success', 'Demande boutique refusée.');

        return $this->redirectToRoute('admin_shop_request_index');
    }
}
