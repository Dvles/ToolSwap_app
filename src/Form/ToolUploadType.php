<?php

namespace App\Form;

use App\Entity\Tool;
use App\Entity\ToolCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ToolUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Tool name cannot be empty.',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Tool name cannot exceed {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('description', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Description cannot be empty.',
                    ]),
                ],
            ])
            ->add('toolCondition', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select the condition of the tool.',
                    ]),
                ],
            ])
            ->add('priceDay', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Price per day cannot be empty.',
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'The price must be a valid number.',
                    ]),
                    new Assert\Positive([
                        'message' => 'The price must be a positive number.',
                    ]),
                ],
            ])
            ->add('imageTool', null, [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please upload an image of the tool.',
                    ]),
                ],
            ])
            ->add('toolCategory', EntityType::class, [
                'class' => ToolCategory::class,
                'choice_label' => 'name',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select a tool category.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tool::class,
        ]);
    }
}
