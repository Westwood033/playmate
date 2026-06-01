<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Item::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Articles')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvel article')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l\'article')
            ->setEntityLabelInPlural('Articles')
            ->setEntityLabelInSingular('Article')
            ->setSearchFields(['name', 'description', 'owner.username', 'owner.email'])
            ->setDefaultSort(['createdAt' => 'DESC']);

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        $categories = [
            'Carte' => 'carte',
            'Booster' => 'booster',
            'Display' => 'display',
            'Autre' => 'autre',
        ];

        $conditions = [
            'Neuf' => 'neuf',
            'Très bon état' => 'tres_bon',
            'Bon état' => 'bon',
            'État correct' => 'correct',
        ];

        return [
            IdField::new('id')
                ->hideOnForm(),

            TextField::new('name', 'Nom')
                ->setColumns(6),

            MoneyField::new('price', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(false)
                ->setColumns(3),

            BooleanField::new('isSold', 'Vendu')
                ->renderAsSwitch(false)
                ->setColumns(3),

            TextField::new('category', 'Catégorie')
                ->formatValue(function (?string $value) use ($categories) {
                    return array_search($value, $categories) ?: ($value ?? '—');
                })
                ->onlyOnIndex(),

            ChoiceField::new('category', 'Catégorie')
                ->setChoices($categories)
                ->setRequired(false)
                ->onlyOnForms()
                ->setColumns(6),

            TextField::new('condition', 'État')
                ->formatValue(function (?string $value) use ($conditions) {
                    return array_search($value, $conditions) ?: ($value ?? '—');
                })
                ->onlyOnIndex(),

            ChoiceField::new('condition', 'État')
                ->setChoices($conditions)
                ->setRequired(false)
                ->onlyOnForms()
                ->setColumns(6),

            TextareaField::new('description', 'Description')
                ->hideOnIndex()
                ->setNumOfRows(5)
                ->setColumns(12),

            AssociationField::new('owner', 'Propriétaire')
                ->setColumns(6),

            DateTimeField::new('createdAt', 'Publié le')
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isSold', 'Vendu'))
            ->add(ChoiceFilter::new('category', 'Catégorie')->setChoices([
                'Carte' => 'carte',
                'Booster' => 'booster',
                'Display' => 'display',
                'Autre' => 'autre',
            ]))
            ->add(ChoiceFilter::new('condition', 'État')->setChoices([
                'Neuf' => 'neuf',
                'Très bon état' => 'tres_bon',
                'Bon état' => 'bon',
                'État correct' => 'correct',
            ]))
            ->add(EntityFilter::new('owner', 'Propriétaire'));
    }
}
