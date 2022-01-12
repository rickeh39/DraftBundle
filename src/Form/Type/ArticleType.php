<?php
namespace App\Form\Type;

use App\Document\Content;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contentName', TextType::class,[
                'attr' => ['data-draft-type' => 'title']
            ])
            ->add('description', TextType::class,[
                'label' => 'Omschrijving',
                'attr' => ['data-draft-type' => 'description']
            ])
            ->add('contentType', TextareaType::class, [
                'attr' => [
                    'class' => 'tinymce',
                    'rows' => 10,
                    'style' => 'width: 100%',
                    'data-draft-type' => 'contentType'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
        ]);
    }
}