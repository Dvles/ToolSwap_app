<?php

namespace App\Form;

use App\Entity\Tool;
use App\Entity\ToolAvailability;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToolAvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // set in controller 
            // ->add('title') ; Tool getName()
            ->add('start', null, [
                'widget' => 'single_text',
            ])
            ->add('end', null, [
                'widget' => 'single_text',
            ])
            
            // set in controller
            // ->add('background_color'); setBackgroundColor(string $background_color)
            // ->add('border_color'); setBorderColor(string $border_color)
            // ->add('text_color'); setTextColor(string $text_color)
            //->add('user', EntityType::class, [
            //    'class' => User::class,
            //    'choice_label' => 'id',
            //])
            //->add('Tool', EntityType::class, [
            //    'class' => Tool::class,
            //    'choice_label' => 'id',
            //])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ToolAvailability::class,
        ]);
    }
}
