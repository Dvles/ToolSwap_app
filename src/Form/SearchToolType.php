<?php

namespace App\Form;

use App\Entity\Tool;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchToolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keyword', TextType::class, [
                'label' => 'Recherche outil',
                'attr' => [
                    'placeholder' => 'Recherche outil',
                    'class' => 'form-control', // Bootstrap class to add later !!!!!!
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Set the data class to null, since we're not binding to an entity
        $resolver->setDefaults([
            
        ]);
    }
}
