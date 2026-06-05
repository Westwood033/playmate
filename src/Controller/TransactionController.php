<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/transactions')]
final class TransactionController extends AbstractController
{
    public function __construct(
        private readonly MailerInterface $mailer,
    )
    {
    }

    /**
     * @throws ApiErrorException
     * @throws TransportExceptionInterface
     */
    #[Route('/new/{id}', name: 'transaction_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request                $request,
        EntityManagerInterface $em,
        Item                   $item
    ): Response
    {
        /** @var User $buyer */
        $buyer = $this->getUser();
        $seller = $item->getOwner();

        if ($buyer === $seller || $item->isSold()) {
            $this->addFlash('error', 'Action impossible.');
            return $this->redirectToRoute('item_show', ['id' => $item->getId()]);
        }

        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('transaction/new.html.twig', [
                'form' => $form,
                'item' => $item,
            ]);
        }

        $price = (int)$item->getPrice();
        $paymentMethod = $form->get('paymentMethod')->getData();

        $addresses = $this->buildAddresses($form);

        if ($paymentMethod === 'wallet') {

            if ($buyer->getWallet() < $price) {
                $this->addFlash('error', 'Solde insuffisant.');
                return $this->redirectToRoute('transaction_new', ['id' => $item->getId()]);
            }

            $this->handleSale($transaction, $item, $buyer, $seller, $price, $addresses);

            $em->persist($transaction);
            $em->flush();

            $this->sendValidationEmail(
                $buyer,
                $item->getName(),
                $item->getPrice(),
                $seller->getUsername(),
                $addresses['address']
            );

            $this->addFlash('success', 'Paiement confirmé. Vous recevrez un email de confirmation.');

            return $this->redirectToRoute('item_show', ['id' => $item->getId()]);
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $price * 100,
                    'product_data' => [
                        'name' => $item->getName(),
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $this->generateUrl(
                'transaction_success',
                [
                    'itemId' => $item->getId(),
                    'buyerId' => $buyer->getId(),
                    'address' => base64_encode($addresses['address']),
                    'billingAddress' => base64_encode($addresses['billingAddress']),
                    'sameAddress' => $addresses['sameAddress'] ? 1 : 0,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'cancel_url' => $this->generateUrl(
                'transaction_new',
                ['id' => $item->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);

        return $this->json(['url' => $session->url]);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TransportExceptionInterface
     */
    #[Route('/success', name: 'transaction_success')]
    public function success(
        Request                $request,
        EntityManagerInterface $em
    ): Response
    {
        $item = $em->find(Item::class, $request->query->get('itemId'));
        $buyer = $em->find(User::class, $request->query->get('buyerId'));

        if (!$item || !$buyer || $item->isSold()) {
            return $this->redirectToRoute('item_index');
        }

        $seller = $item->getOwner();

        $addresses = [
            'address' => base64_decode($request->query->get('address')),
            'billingAddress' => base64_decode($request->query->get('billingAddress')),
            'sameAddress' => (bool)$request->query->get('sameAddress'),
        ];

        if ($addresses['sameAddress']) {
            $addresses['billingAddress'] = $addresses['address'];
        }

        $transaction = new Transaction();

        $this->handleSale(
            $transaction,
            $item,
            $buyer,
            $seller,
            (int)$item->getPrice(),
            $addresses
        );

        $em->persist($transaction);
        $em->flush();

        $this->sendValidationEmail(
            $buyer,
            $item->getName(),
            $item->getPrice(),
            $seller->getUsername(),
            $addresses['address']
        );

        $this->addFlash('success', 'Paiement confirmé. Vous recevrez un email de confirmation.');

        return $this->redirectToRoute('item_show', ['id' => $item->getId()]);
    }

    private function buildAddresses($form): array
    {
        $address = (string)$form->get('address')->getData();
        $sameAddress = (bool)$form->get('sameAddress')->getData();
        $billingAddress = $form->get('billingAddress')->getData();

        if ($sameAddress) {
            $billingAddress = $address;
        }

        return [
            'address' => $address,
            'billingAddress' => $billingAddress,
            'sameAddress' => $sameAddress,
        ];
    }

    private function handleSale(
        Transaction $transaction,
        Item        $item,
        User        $buyer,
        User        $seller,
        int         $price,
        array       $addresses
    ): void
    {
        $buyer->removeFromWallet($price);
        $seller->addToWallet($price);

        $transaction->setBuyer($buyer);
        $transaction->setSeller($seller);
        $transaction->setItem($item);
        $transaction->setTransactedAt(new DateTimeImmutable());

        $transaction->setAddress($addresses['address']);
        $transaction->setBillingAddress($addresses['billingAddress']);
        $transaction->setSameAddress($addresses['sameAddress']);

        $item->setIsSold(true);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendValidationEmail(
        User   $buyer,
        string $itemName,
        string $itemPrice,
        string $sellerName,
        string $address
    ): void
    {
        $email = (new TemplatedEmail())
            ->from('noreply@playmate.fr')
            ->to($buyer->getEmail())
            ->subject('Achat confirmé')
            ->htmlTemplate('transaction/email/success.html.twig')
            ->context([
                'itemName' => $itemName,
                'itemPrice' => $itemPrice,
                'itemSeller' => $sellerName,
                'buyerAddress' => $address,
            ]);

        $this->mailer->send($email);
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
