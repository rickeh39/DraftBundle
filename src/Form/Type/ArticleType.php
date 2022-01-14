<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['data']['contentTypes'] as $item){
            if ($item->getTypeName() == 'Content'){
                $builder->add('content',
                    'Symfony\Component\Form\Extension\Core\Type\\'.$item->getTypeFormBuild(), [
                    'data' => $options['data']['contentValues'][$item->getTypeName()] ?? '',
                    'attr' => [
                        'class' => 'tinymce',
                        'rows' => 10,
                        'style' => 'width: 100%',
                        'data-draft-type' => $item->getTypeName()
                    ],
                ]);
            } else {
                $builder
                    ->add($item->getTypeName(),
                        'Symfony\Component\Form\Extension\Core\Type\\'.$item->getTypeFormBuild(),[
                        'data' => $options['data']['contentValues'][$item->getTypeName()] ?? '',
                        'attr' => [
                            'data-draft-type' => $item->getTypeName(),
                        ]
                    ]);
            }
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}