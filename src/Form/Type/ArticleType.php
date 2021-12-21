<?php
namespace App\Form\Type;

use App\Document\Article;
use Doctrine\ODM\MongoDB\Types\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends \Symfony\Component\Form\AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['data-article-type' => 'title']
            ])
            ->add('description', TextType::class, [
                'attr' => ['data-article-type' => 'description']
            ])
            ->add('content', TextareaType::class, [
                'attr' => ['data-article-type' => 'content']
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}