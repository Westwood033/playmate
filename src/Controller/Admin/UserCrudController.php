<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setEntityLabelInSingular('Utilisateur')
            ->setSearchFields(['email', 'username', 'firstname', 'lastname', 'email'])
            ->setDefaultSort(['username' => 'ASC']);

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('username', 'Nom d\'utilisateur'),
            TextField::new('email', 'Email'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->onlyOnForms(),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Administrateur' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER',
                    'Boutique' => 'ROLE_SHOP'
                ])
                ->allowMultipleChoices()
                ->hideOnIndex(),
            ArrayField::new('roles', 'Rôles')
                ->formatValue(function ($value, $entity) {
                    $labels = [
                        'ROLE_ADMIN' => 'Administrateur',
                        'ROLE_USER' => 'Utilisateur',
                        'ROLE_SHOP' => 'Boutique',
                    ];
                    return array_map(
                        fn($role) => $labels[$role] ?? $role,
                        $entity->getRoles()
                    );
                })
                ->setTemplatePath('admin/field/roles_badge.html.twig')
                ->onlyOnIndex(),
        ];
    }
}
