<?php

namespace App\Form;

use App\Entity\BorrowTool;
use App\Entity\Tool;
use App\Entity\User;
use App\Enum\ToolStatusEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BorrowToolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text', // For a proper date picker in HTML5
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text', // For a proper date picker in HTML5
            ])
            ->add('status', ChoiceType::class, [
                'choices' => ToolStatusEnum::cases(), // Use enum cases for choices
                'choice_label' => function($choice) {
                    return $choice->value; // Display enum values
                },
            ])
            ->add('userBorrower', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id', // You can customize how users are displayed
                'disabled' => true, // Make this field non-editable if it's set by the controller
            ])
            ->add('toolBeingBorrowed', EntityType::class, [
                'class' => Tool::class,
                'choice_label' => 'id', // You can customize how tools are displayed
                'disabled' => true, // Make this field non-editable if it's set by the controller
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BorrowTool::class,
        ]);
    }
}
