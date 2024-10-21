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
    #[Route('/borrow/tool', name: 'app_borrow_tool')]
    public function index(): Response
    {
        return $this->render('borrow_tool/index.html.twig', [
            'controller_name' => 'BorrowToolController',
        ]);
    }

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
            error_log(print_r($content, true));

            if (!isset($content['availabilities'])) {
                return new JsonResponse(['error' => 'No availabilities provided'], 400);
            }

            // Redirect if the user is not logged in
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute("app_login");
            }

            foreach ($content['availabilities'] as $borrowToolAvailability) {
                // Check if required keys exist
                if (!isset($borrowToolAvailability['start'], $borrowToolAvailability['end'], $borrowToolAvailability['toolId'], $borrowToolAvailability['id'])) {
                    return new JsonResponse(['error' => 'Missing required fields'], 400);
                }

                $startDate = new \DateTime($borrowToolAvailability['start']);
                $endDate = new \DateTime($borrowToolAvailability['end']);
                $toolId = $borrowToolAvailability['toolId'];
                $toolAvailability = $borrowToolAvailability['id'];

                // STEP 2 - Create BorrowTool objects
                $borrowTool = new BorrowTool();
                $borrowTool->setStartDate($startDate);
                $borrowTool->setEndDate($endDate);
                $borrowTool->setStatus(ToolStatusEnum::PENDING);
                $borrowTool->setUserBorrower($user);
                $borrowTool->setToolBeingBorrowed($manager->getRepository(Tool::class)->find($toolId)); // fetch the tool entity
                $borrowTool->setToolAvailability($manager->getRepository(ToolAvailability::class)->find($toolAvailability)); // fetch the availability entity

                $manager->persist($borrowTool);

                // STEP 3 - Change ToolAvailability status
                $availabilityEntity = $borrowTool->getToolAvailability();
                if ($availabilityEntity) {
                    $availabilityEntity->setIsAvailable(false); // mark the tool as unavailable
                    $manager->persist($availabilityEntity); // persist the change
                }
            }

            $manager->flush();

            return $this->redirectToRoute('tool_borrow_success', ['tool_id' => $tool_id]);
        } catch (\Exception $e) {
            error_log($e->getMessage()); // Log any exceptions
            return new JsonResponse(['error' => 'Something went wrong'], 500);
        }
    }


    #[Route('/tool/single/{tool_id}/borrow/success', name: 'tool_borrow_success')]
    public function borrowSuccess($tool_id)
    {
        return $this->render('borrow_tool/tool_borrow_success.html.twig', [
            'tool_id' => $tool_id,
        ]);
    }
}
