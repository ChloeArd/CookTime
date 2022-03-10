<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            UrlField::new('picture'),
            TimeField::new('time'),
            ChoiceField::new('level')->setChoices([
                'Très facile' => "Très facile",
                'Facile' => "Facile",
                'Moyen' => "Moyen",
                'Difficile' => "Difficile",
                'Très difficile' => "Très difficile",
            ]),
            TextareaField::new('preparation'),
            DateTimeField::new('date')->hideOnForm(),
            AssociationField::new('user')
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Article) {
            return;
        }
        date_default_timezone_set('Europe/Paris');
        $entityInstance->setDate(new \DateTime());
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Article) {
            return;
        }
        foreach ($entityInstance->getComments() as $comment) {
            $entityManager->remove($comment);
        }
        parent::deleteEntity($entityManager, $entityInstance);
    }
}
