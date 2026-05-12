<?php

namespace App\Form;

use App\Entity\Tournament;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)

            ->add('description', TextareaType::class)

            ->add('tournamentDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de l\'évènement',
            ])

            ->add('participantNumber', IntegerType::class, [
                'label' => 'Nombre de participant maximum',
                'attr' => [
                    'min' => 1,
                ],
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 1,
                        'message' => 'Le nombre de participants doit être au minimum de 1.',
                    ]),
                ],
            ])

            ->add('street', TextType::class, [
                'mapped' => false,
                'label' => 'Où se situe l\'évènement',
            ])

            ->add('postalCode', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\Regex([
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}