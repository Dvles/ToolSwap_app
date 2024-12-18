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
                'label' => 'Catégorie',
                'placeholder' => '- Sélectionner -',
                'required' => false,
            ])
            // Add community selection dropdown
            ->add('community', ChoiceType::class, [
                'choices' => $options['communities'],  // Uses communityChoices with name as label, ID as value
                'label' => 'Commune',
                'placeholder' => '- Sélectionner -',
                'required' => false,
            ])
            // Add checkbox for filtering free tools
            ->add('isFree', CheckboxType::class, [
                'label' => 'Gratuit',
                'required' => false,
            ])
            
            ->add('search', SubmitType::class, [ // Add submit button
                'label' => 'Filtrer', 
                'attr' => [
                    'class' => 'btn btn-primary', // Add Bootstrap class for styling
                ],
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
