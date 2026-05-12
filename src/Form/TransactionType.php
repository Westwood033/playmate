<?php

namespace App\Form;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('billingAddress', TextType::class, [
                'label' => 'Adresse de facturation',
                'attr' => ['placeholder' => '2 rue du Fromage, 12345 Moule, France'],
            ])
            ->add('sameAddress', Boolean::class, [
                'label' => 'L\'adresse de facturation et la même que l\'adresse de livraison',
                'attr' => ['placeholder' => false],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransactionType::class,
        ]);
    }
}
