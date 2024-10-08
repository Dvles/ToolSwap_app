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
        
        // creating dropdown ensuring users can only select available dates
        ->add('toolAvailability', EntityType::class, [
            'class' => ToolAvailability::class,
            'query_builder' => function (EntityRepository $er) use ($options) {
                return $er->createQueryBuilder('ta')
                    ->where('ta.isAvailable = :available') // Only get available tool availabilities
                    ->setParameter('available', true) // Set the parameter to true to filter available records
                    ->andWhere('ta.tool = :tool') // Ensure you filter by the specific tool
                    ->setParameter('tool', $options['tool']) // Set the tool parameter
                    ->orderBy('ta.start', 'ASC'); // UX enhancement =) logic
            },
            'placeholder' => 'Select Availability',
            'choice_label' => function (ToolAvailability $availability) {
                return $availability->getStart()->format('d-m-Y'); 
            },
        ]);


        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BorrowTool::class,
            'tool' => null, // Add a new option for the selected tool

        ]);
    }
}
