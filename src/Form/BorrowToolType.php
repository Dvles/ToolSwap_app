<?php

namespace App\Form;

use App\Entity\BorrowTool;
use App\Entity\ToolAvailability;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BorrowToolType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
        // creating drowpdown ensuring users can only select available dates
        ->add('toolAvailability', EntityType::class, [
            'class' => ToolAvailability::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('ta')
                ->orderBy('ta.start', 'ASC');
            },
            'choice_label' => function (ToolAvailability $availability) {
                return $availability->getStart()->format('d-m-Y'); 
            },
            'placeholder' => 'Select Availability',
        ]);

        // values no longer needed

        // ->add('startDate', null, [
        //     'widget' => 'single_text',
        // ])
        // ->add('endDate', null, [
        //     'widget' => 'single_text',
        // ])
            
            // values set in backend

            //->add('status')
            // ->add('userBorrower', EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('toolBeingBorrowed', EntityType::class, [
            //     'class' => Tool::class,
            //     'choice_label' => 'id',
            // ])
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BorrowTool::class,

        ]);
    }
}
