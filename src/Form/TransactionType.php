<?php

namespace App\Form;

use App\Entity\Transaction;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('billingAddress', TextType::class, [
                'label' => 'Adresse de facturation',
                'attr' => ['placeholder' => '2 rue du Lila, 12345 Moute, France'],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => ['placeholder' => '2 rue du Lila, 12345 Moute, France'],
            ])
            ->add(
                'sameAddress',
                CheckboxType::class,
                [
                    'label' => 'L\'adresse de facturation et la même que l\'adresse de livraison',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
