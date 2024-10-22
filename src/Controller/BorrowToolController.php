<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\User;
use App\Entity\BorrowTool;
use App\Entity\ToolAvailability;
use App\Enum\ToolStatusEnum;
use App\Form\BorrowToolType;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;




class BorrowToolController extends AbstractController
{


    #[Route('/tool/single/{tool_id}/borrow', name: 'tool_borrow')]
    public function toolBorrow(ManagerRegistry $doctrine, Request $request, $tool_id): Response
    {
        // Redirect if the user is not logged in
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute("app_login");
        }

        // Fetch the specific tool from the database using the tool ID
        $tool = $doctrine->getRepository(Tool::class)->find($tool_id);
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }


        // Fetch ToolAvailability repository
        $repToolAvailability = $doctrine->getRepository(ToolAvailability::class);

        // Deactivate expired ToolAvailabilities
        $repToolAvailability->deactivateExpiredAvailabilities();


        // Create a new BorrowTool entity and pre-fill user and tool
        $borrowTool = new BorrowTool();

        // Set default values
        $borrowTool->setUserBorrower($user); // Set the current user as the borrower
        $borrowTool->setToolBeingBorrowed($tool); // Set the current tool being borrowed
        $borrowTool->setStatus(ToolStatusEnum::PENDING); // Set default status to "pending"

        // Create the form and handle the request
        $form = $this->createForm(BorrowToolType::class, $borrowTool, [
            'tool' => $tool, // Pass the current tool being borrowed
        ]);
        $form->handleRequest($request);

        // Deactivate expired toolAvailability


        // If the form is submitted and valid, save the borrow tool request
        if ($form->isSubmitted() && $form->isValid()) {

            // Get the selected ToolAvailability from the form
            $toolAvailability = $borrowTool->getToolAvailability();

            // Automatically set the start and end dates based on the selected availability
            if ($toolAvailability) {
                $borrowTool->setStartDate($toolAvailability->getStart());
                $borrowTool->setEndDate($toolAvailability->getEnd());
            }

            // Set the ToolAvailability to be unavailable (-> so it's filtered out from BorrowTool dropdown of ToolAvailabilities)
            $toolAvailability->setIsAvailable(false);
            $doctrine->getManager()->persist($toolAvailability);

            $em = $doctrine->getManager();
            $em->persist($borrowTool);
            $em->flush();


            // Redirect to some success or tool detail page
            return $this->redirectToRoute('tool_display_single', ['tool_id' => $tool_id]);
        }

        // Render the form and tool information
        return $this->render('borrow_tool/tool_borrow.html.twig', [
            'form' => $form->createView(),
            'tool' => $tool,
        ]);
    }

    #[Route('/tool/single/{tool_id}/borrow/calendar', name: 'tool_borrow_calendar')]
    public function toolBorrowCalendar(EntityManagerInterface $em, SerializerInterface $serializer, ManagerRegistry $doctrine, Request $request, $tool_id): Response
    {
        // Redirect if the user is not logged in
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute("app_login");
        }

        // Fetching the user with associated data
        $userFromDb = $em->getRepository(User::class)->find($user->getId());

        // Validate that the user exists and is valid
        if (!$userFromDb) {
            throw $this->createNotFoundException('User not found.');
        }

        // dd($userFromDb); 

        // Fetch the specific tool from the database using the tool ID
        $tool = $doctrine->getRepository(Tool::class)->find($tool_id);
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }


        // Fetch ToolAvailability repository
        $repToolAvailability = $doctrine->getRepository(ToolAvailability::class);

        // Deactivate expired ToolAvailabilities
        $repToolAvailability->deactivateExpiredAvailabilities();

        //dd($tool);

        // Get tool availabilities for the tool
        $toolAvailabilities = $tool->getToolAvailabilities();

        // Force initialization of the PersistentCollection if not already initialized
        if ($toolAvailabilities instanceof PersistentCollection && !$toolAvailabilities->isInitialized()) {
            $toolAvailabilities->initialize();
        }

        //dd($toolAvailabilities->count());

        // Filter availabilities to include only those that are available
        $availableToolAvailabilities = array_filter($toolAvailabilities->toArray(), function ($availability) {
            return $availability->isAvailable() === true;
        });

        // After filtering availableToolAvailabilities
        $availableToolAvailabilities = array_filter($toolAvailabilities->toArray(), function ($availability) {
            return $availability->isAvailable() === true;
        });

        // Check if there are available tool availabilities
        if (empty($availableToolAvailabilities)) {
            // Optionally inform the user that no availabilities are found
            // Add a flash message here if needed
            return $this->redirectToRoute("app_login");
        }

        //dd($availableToolAvailabilities);

        // Instead of using serialize on the filtered results directly
        $toolAvailabilitiesJSON = $serializer->serialize(
            array_values($availableToolAvailabilities), // Convert associative array to indexed array
            'json',
            [AbstractNormalizer::GROUPS => ['tool:read']]
        );

        //dd($toolAvailabilitiesJSON);

        $vars = [
            'toolAvailabilitiesJSON' => $toolAvailabilitiesJSON,
            'tool' => $tool
        ];
        return $this->render('borrow_tool/tool_borrow_calendar.html.twig', $vars);
    }

    #[Route('/tool/single/{tool_id}/borrow/calendar/confirm', name: 'tool_borrow_calendar_confirm', methods: ['POST'])]
    public function toolBorrowCalendarConfirm($tool_id, Request $request, EntityManagerInterface $manager)
    {
        try {
            // Log incoming request data
            $content = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            // Log the entire content for debugging
            error_log('Incoming request data: ' . print_r($content, true));

            if (!isset($content['availabilities'])) {
                return new JsonResponse(['error' => 'No availabilities provided'], 400);
            }

            // Redirect if the user is not logged in
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute("app_login");
            }

            // **** Sort availabilities by start date using usort()
            // The comparison function converts the 'start' date strings to Unix timestamps 
            // and returns the difference between them. This allows usort() to order the 
            // availabilities based on the start date in ascending order.
            usort($content['availabilities'], function ($a, $b) {
                return strtotime($a['start']) - strtotime($b['start']);
            });

            // Initialize variables for grouping consecutive availabilities
            $startDate = null;
            $endDate = null;
            $currentBorrowTool = null;

            // Fetch the tool entity
            $toolBeingBorrowed = $manager->getRepository(Tool::class)->find($tool_id);
            if (!$toolBeingBorrowed) {
                error_log('Tool not found with ID: ' . $tool_id);
                return new JsonResponse(['error' => 'Tool not found'], 404);
            }

            // Iterate through the availabilities
            foreach ($content['availabilities'] as $borrowToolAvailability) {
                // Check if required keys exist
                if (!isset($borrowToolAvailability['start'], $borrowToolAvailability['end'], $borrowToolAvailability['toolId'], $borrowToolAvailability['id'])) {
                    return new JsonResponse(['error' => 'Missing required fields'], 400);
                }

                $currentStartDate = new \DateTime($borrowToolAvailability['start']);
                $currentEndDate = new \DateTime($borrowToolAvailability['end']);
                $toolAvailabilityId = $borrowToolAvailability['id'];

                // Log the current availability being processed
                error_log("Processing availability: Start - {$currentStartDate->format('Y-m-d H:i:s')}, End - {$currentEndDate->format('Y-m-d H:i:s')}");

                // Check if we need to create a new BorrowTool
                if ($currentBorrowTool === null) {
                    // First availability, create a new BorrowTool
                    $currentBorrowTool = new BorrowTool();
                    $currentBorrowTool->setUserBorrower($user);
                    $currentBorrowTool->setToolBeingBorrowed($toolBeingBorrowed);
                    $currentBorrowTool->setStatus(ToolStatusEnum::PENDING);
                    $startDate = $currentStartDate;
                    $endDate = $currentEndDate;
                } elseif ($endDate->modify('+1 day') == $currentStartDate) {
                    // If consecutive, extend the end date
                    $endDate = $currentEndDate;
                } else {
                    // Not consecutive, finalize the previous BorrowTool
                    $this->finalizeBorrowTool($currentBorrowTool, $startDate, $endDate, $manager);

                    // Create a new BorrowTool for the current availability
                    $currentBorrowTool = new BorrowTool();
                    $currentBorrowTool->setUserBorrower($user);
                    $currentBorrowTool->setToolBeingBorrowed($toolBeingBorrowed);
                    $currentBorrowTool->setStatus(ToolStatusEnum::PENDING);
                    $startDate = $currentStartDate;
                    $endDate = $currentEndDate;
                }

                // Fetch the ToolAvailability entity
                $toolAvailability = $manager->getRepository(ToolAvailability::class)->find($toolAvailabilityId);
                if (!$toolAvailability) {
                    error_log("ToolAvailability not found for ID: {$toolAvailabilityId}");
                    return new JsonResponse(['error' => 'ToolAvailability not found'], 404);
                }

                // Mark availability as unavailable and associate with the current BorrowTool
                $toolAvailability->setIsAvailable(false);
                $toolAvailability->setBorrowTool($currentBorrowTool);
                $manager->persist($toolAvailability);
            }

            // Finalize the last group of availabilities
            if ($currentBorrowTool !== null && $startDate !== null && $endDate !== null) {
                $this->finalizeBorrowTool($currentBorrowTool, $startDate, $endDate, $manager);
            }

            // Save all changes to the database
            $manager->flush();

            return $this->redirectToRoute('tool_borrow_success', ['tool_id' => $tool_id]);
        } catch (\Exception $e) {
            // Log the exception with stack trace for more context
            error_log('Error in toolBorrowCalendarConfirm: ' . $e->getMessage());
            error_log($e->getTraceAsString());
            return new JsonResponse(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Finalizes the borrow tool settings and persists it to the database.
     */
    private function finalizeBorrowTool(BorrowTool $borrowTool, \DateTime $startDate, \DateTime $endDate, EntityManagerInterface $manager)
    {
        $borrowTool->setStartDate($startDate);
        $borrowTool->setEndDate($endDate);
        $manager->persist($borrowTool);

        // Log the completed group of availabilities
        error_log("Saving borrow tool from {$startDate->format('Y-m-d H:i:s')} to {$endDate->format('Y-m-d H:i:s')}");
    }

    #[Route('/tool/single/{tool_id}/borrow/success', name: 'tool_borrow_success')]
    public function borrowSuccess($tool_id)
    {
        return $this->render('borrow_tool/tool_borrow_success.html.twig', [
            'tool_id' => $tool_id,
        ]);
    }

    #[Route('/tool/borrow/display', name: 'tool_borrow_display')]
    public function borrowToolUser(ManagerRegistry $doctrine)
    {

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }

        // Fetch the EntityManager
        $em = $doctrine->getManager();

        // Fetch the user with their borrows using a Doctrine query to ensure the relation is loaded
        $toolOfBorrowTools = $em->getRepository(User::class)->find($user->getId());

        // Check if tools are fetched
        $BorrowTools = $toolOfBorrowTools->getBorrowTools();

        // Prepare borrowTool object data to avoid lazy loading and errors
        $borrowToolsData = [];
        foreach ($BorrowTools as $BorrowTool) {
            
            $borrowToolsData[] = [
                'id' => $BorrowTool->getId(),
                'tool' => $BorrowTool->getToolBeingBorrowed()->getName(),
                'start' => $BorrowTool->getStartDate()->format('d-m-Y'),
                'end' => $BorrowTool->getEndDate()->format('d-m-Y'),
                'status' => $BorrowTool->getStatus()->value

            ];
        }

        //dd($borrowToolsData);

        $vars = ['borrowTools' => $borrowToolsData];

        return $this->render('borrow_tool/tool_borrow_display.html.twig', $vars);
    }

    #[Route('/tool/borrow/lending/display', name: 'tool_borrow_lending_display')]
    public function borrowToolLendingUser(ManagerRegistry $doctrine)
    {

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }

        // Fetch the EntityManager
        $em = $doctrine->getManager();

        $userLendingTool = $em->getRepository(User::class)->find($user->getId());
        $userTools = $userLendingTool ->getToolsOwned();

        foreach ($userTools as $userTool){

            // Check if tools are fetched
            $BorrowTools = $userTool->getBorrowTools();

            // Prepare borrowTool object data to avoid lazy loading and errors
            $borrowToolsData = [];
            foreach ($BorrowTools as $BorrowTool) {

                $start = $BorrowTool->getStartDate();
                $end = $BorrowTool->getEndDate();
        
                // Calculate the difference between start and end dates
                $dateInterval = $start->diff($end);
                $days = $dateInterval->days; // Get the total number of days
                
                $borrowToolsData[] = [
                    'userBorrower' => $BorrowTool->getUserBorrower()->getFirstName(),
                    'id' => $BorrowTool->getId(),
                    'tool' => $BorrowTool->getToolBeingBorrowed()->getName(),
                    'start' => $start->format('d-m-Y'),
                    'end' => $end->format('d-m-Y'),
                    'status' => $BorrowTool->getStatus()->value,
                    'days' => $days,
                    'toolID' => $BorrowTool->getStatus()->value
    
                ];
            }

        }

        //dd($borrowToolsData);

        $vars = ['borrowTools' => $borrowToolsData];

        return $this->render('borrow_tool/tool_borrow_lending_display.html.twig', $vars);
    }
}
