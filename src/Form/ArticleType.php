<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\DBAL\Types\IntegerType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateTime = new \DateTime();
        $builder
            ->add('title', TextType::class, ['label' => 'Title of article'])
            ->add('picture', UrlType::class, ['label' => 'URL to photo of the dish'])
            ->add('time', TimeType::class, [
                'label' => 'Preparation + cooking time',
                'widget' => 'single_text',
            ])
            ->add('level', TextType::class, ['label' => 'The level'])
            ->add('preparation', CKEditorType::class, [
                'label' => 'Preparing the recipe',
                'constraints' => [
                    new Length([
                        'min' => 40,
                        'max' => 3000,
                        'minMessage' => 'The preparation is too short',
                        'maxMessage' => 'The preparation is too long'
                    ])
                ]
            ])
            ->add('date', DateTimeType::class, ['widget' => 'single_text', 'required' => false])
            ->add('user', EntityType::class, ['class' => User::class, "choice_label" => "id"])
            ->add('submit', SubmitType::class, ["label" => "Save"] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
