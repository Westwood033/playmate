<?php

namespace App\Controller\Admin\Crud;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ShopRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Requêtes boutiques')
            ->setEntityLabelInPlural('Boutiques')
            ->setEntityLabelInSingular('Boutique')
            ->setSearchFields(['username', 'shopName', 'shopAddress', 'phone'])
            ->setDefaultSort(['username' => 'ASC']);

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $accept = Action::new('accept', 'Accepter')
            ->linkToRoute('admin_shop_validate', fn(User $u) => ['id' => $u->getId()])
            ->addCssClass('btn-success');

        $reject = Action::new('reject', 'Rejeter')
            ->linkToRoute('admin_shop_reject', fn(User $u) => ['id' => $u->getId()])
            ->addCssClass('btn-danger');

        return $actions
            ->disable(Crud::PAGE_NEW, Crud::PAGE_EDIT, Crud::PAGE_DETAIL, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $accept)
            ->add(Crud::PAGE_INDEX, $reject);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('username', 'Utilisateur'),
            TextField::new('shopName', 'Boutique'),
            TextField::new('shopAddress', 'Adresse'),
            TextField::new('phone', 'Téléphone'),
        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.shopRequest = true');
    }
}
