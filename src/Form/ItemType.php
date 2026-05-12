<?php

namespace App\Form;

use App\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'article',
                'attr' => ['placeholder' => 'Ex : Carte Dracaufeu'],
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.')],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR',
                'attr' => ['placeholder' => '0.00'],
                'constraints' => [
                    new NotBlank(message: 'Le prix est obligatoire.'),
                    new Positive(message: 'Le prix doit être positif.'),
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'placeholder' => '— Choisir —',
                'required' => false,
                'choices' => [
                    'Carte' => 'carte',
                    'Booster' => 'booster',
                    'Display' => 'display',
                    'Autre' => 'autre',
                ],
            ])
            ->add('condition', ChoiceType::class, [
                'label' => 'État',
                'placeholder' => '— Choisir —',
                'required' => false,
                'choices' => [
                    'Neuf' => 'neuf',
                    'Très bon état' => 'tres_bon',
                    'Bon état' => 'bon',
                    'État correct' => 'correct',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Décrivez votre article : état, taille, défauts éventuels…',
                    'rows' => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}
