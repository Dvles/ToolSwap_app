<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToolFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Add category selection dropdown
        $builder
            ->add('category', ChoiceType::class, [
                'choices' => $options['categories'], // 'categories' will be passed from the controller
                'placeholder' => 'Sélectionner',
                'required' => false,
            ])
            // Add community selection dropdown
            ->add('community', ChoiceType::class, [
                'choices' => $options['communities'], 
                'placeholder' => 'Sélectionner',
                'required' => false,
            ])
            // Add checkbox for filtering free tools
            ->add('isFree', CheckboxType::class, [
                'label' => 'Gratuit',
                'required' => false,
            ])
            // Submit button
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'categories' => [],
            'communities' => [],
        ]);
    }
}
