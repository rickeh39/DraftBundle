<?php
namespace App\Form\Type;

use App\Document\ContentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('selectedItems', ChoiceType::class,[
                'label' => 'Selecteer één of meerdere velden die je wil gebruiken:',
                'attr' => ['data-draft-type' => 'title'],
                'choices' => $options['data'],
                'choice_label' => function(?ContentType $contentType) {
                    return $contentType ? $contentType->getTypeName() : '';
                },
                'expanded' => true,
                'multiple' => true
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Doorgaan naar het aanmaken van het artikel',
                'attr' => [
                    'class' => 'btn bg-primary mt-3'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}