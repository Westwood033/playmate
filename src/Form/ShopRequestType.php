<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ShopRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shopName', null, [
                'label' => 'Nom de la boutique',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer le nom de la boutique',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                    ]),
                ],
            ])

             ->add('street', TextType::class, [
                'mapped' => false,
                'label' => 'Où se situt votre boutique',
            ])

            ->add('postalCode', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Le code postal doit contenir exactement 5 chiffres.',
                    ]),
                ],
            ])

            ->add('city', TextType::class, [
                'mapped' => false,
            ])

            ->add('country', TextType::class, [
                'mapped' => false,
            ])

            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un numéro de téléphone',
                    ]),
                     new Regex([
                        'pattern' => '/^\d{2}(?: \d{2}){4}$/',
                        'message' => 'Le numéro doit contenir exactement 10 chiffres.',
                    ]),
                ],

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}