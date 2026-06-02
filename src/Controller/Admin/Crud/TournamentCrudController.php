<?php

namespace App\Controller\Admin\Crud;

use App\Entity\Tournament;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TournamentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tournament::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Tournois')
            ->setEntityLabelInPlural('Tournois')
            ->setEntityLabelInSingular('Tournoi')
            ->setSearchFields(['name', 'address'])
            ->setDefaultSort(['tournamentDate' => 'ASC']);

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextField::new('address', 'Adresse'),
            DateTimeField::new('tournamentDate', 'Date'),
            IntegerField::new('participantNumber', 'Nombre de participants')
        ];
    }
}
