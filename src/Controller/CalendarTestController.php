<?php

namespace App\Controller;

use App\Entity\BorrowTool;
use App\Entity\Tool;
use App\Entity\ToolAvailability;
use App\Entity\User;
use App\Enum\ToolStatusEnum;
use App\Form\BorrowToolType;
use App\Form\ToolAvailabilityType;
use App\Form\ToolUploadType;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CalendarTestController extends AbstractController
{
    #[Route('/calendar/test', name: 'app_calendar_test')]
    public function index(): Response
    {
        return $this->render('calendar_test/index.html.twig', [
            'controller_name' => 'CalendarTestController',
        ]);
    }

    #[Route('tool/add', name: 'tool_add')]
    public function addTool(Request $request, EntityManagerInterface $em)
    {
        $tool = new Tool();
        $form = $this->createForm(ToolUploadType::class, $tool);
        
        $form->handleRequest($request);
    
        // Handle form submission
        if ($form->isSubmitted()) {
            // Check if the user is logged in
            $user = $this->getUser();
            if (!$user) {
                // Handle the case where the user is not logged in
                throw $this->createAccessDeniedException('You must be logged in to add a tool.');
            }
    
            if (!$form->isValid()) {
                $errors = $form->getErrors(true); 
                foreach ($errors as $error) {
                    echo 'Error: ' . $error->getMessage() . "<br>";
                }
            } else {
                // Set the current user as the owner
                $tool->setOwner($user);
                $em->persist($tool);
                $em->flush();
    
                // Redirect to the tool availability page, passing the tool ID
                return $this->redirectToRoute('tool_add_availability', ['tool_id' => $tool->getId()]);
            }
        }
    
        // Pass form and tool to the view
        return $this->render('calendar_test/tool_add.html.twig', [
            'form' => $form->createView(),
            'tool' => $tool
        ]);
    }
    
    


    #[Route('tool/add/availability/{tool_id}', name: 'tool_add_availability')]
    public function addToolAvailability(Request $request, EntityManagerInterface $em, $tool_id)
    {
        // Find the tool using the tool_id
        $tool = $em->getRepository(Tool::class)->find($tool_id);
    
        // Check if the tool exists
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found.');
        }
    

        // Check if the request is a POST request
        if ($request->isMethod('POST')) {
            // Get the JSON data from the request
            $data = json_decode($request->getContent(), true);

            // Validate the incoming data
            if (empty($data) || !is_array($data)) {
                return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
            }
    
            // Loop through each availability data item
            foreach ($data as $availabilityData) {

                // Validate required fields for each availability
                if (!isset($availabilityData['start']) || !isset($availabilityData['end']) || !isset($availabilityData['title'])) {
                    return $this->json(['error' => 'Missing required fields.'], 400);
                }

                // Create a new ToolAvailability entity for each item
                $toolAvailability = new ToolAvailability();
                $toolAvailability->setTool($tool);
                $toolAvailability->setTitle($availabilityData['title']);
                $toolAvailability->setUser($this->getUser());
                $toolAvailability->setStart(new \DateTime($availabilityData['start']));
                $toolAvailability->setEnd(new \DateTime($availabilityData['end']));
                
                // Set colors from the incoming data if they exist, else set default values
                $toolAvailability->setBackgroundColor($availabilityData['backgroundColor'] ?? 'rgba(255, 179, 71, 1)');
                $toolAvailability->setBorderColor($availabilityData['borderColor'] ?? 'rgba(255, 140, 0, 1)');
                $toolAvailability->setTextColor($availabilityData['textColor'] ?? '#000000');
                

                // Persist the new ToolAvailability
                $em->persist($toolAvailability);
            }


            $em->flush();
            $createdIds[] = $toolAvailability->getId(); // Collect created IDs for UI
    
            // Return a response (you can return the new availability's ID or just a success message)
            return $this->json(['id' => $toolAvailability->getId()], Response::HTTP_CREATED);
        }
    
        return $this->render('calendar_test/tool_add_availability.twig', [
            'tool' => $tool // Pass the tool if you want to display it in the view
        ]);
    }
    


    #[Route('tool/delete/availability/', name: "tool_availability_delete")]
    public function deleteToolAvailability (Request $req, ManagerRegistry $doctrine) : Response {

        $toolAvailabilityObject = json_decode($req->getContent());
        $idDelete = $toolAvailabilityObject->id;
        

        $em = $doctrine->getManager();
        $rep = $em->getRepository(ToolAvailability::class);
        $toolAvailability = $rep->find($idDelete);
        $em->remove($toolAvailability);
        $em->flush();



        return new Response ("Availability deleted", 200);
    }

    



    #[Route('/display/tool/calendar', name: 'display_tool_calendar')]
    public function afficherCalendrierUtilisateur(EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        // check if user is connected or redirected for now
        $user = $this->getUser(); 
        if (is_null($user)) {
            return $this->redirectToRoute("app_login");
        }

        // Fetching the user with associated data
        $userFromDb = $em->getRepository(User::class)->find($user->getId());

        // Validate that the user exists and is valid
        if (!$userFromDb) {
            throw $this->createNotFoundException('User not found.');
        }

        //dd($userFromDb); 
    
        // Get tool availabilities for the user
        $toolAvailabilities = $userFromDb->getToolAvailabilities();

        // Force initialization of the PersistentCollection if not already initialized
        if ($toolAvailabilities instanceof PersistentCollection && !$toolAvailabilities->isInitialized()) {
            $toolAvailabilities->initialize();
        }

        if ($toolAvailabilities->isEmpty()) {
            // Optionally , later add a message to inform user no availabilities found
            return $this->redirectToRoute("app_login"); 
        }
        
    
        // Serialize the availabilities for the JSON response
        $toolAvailabilitiesJSON = $serializer->serialize(
            $toolAvailabilities, 
            'json', 
            [AbstractNormalizer::GROUPS => ['tool:read']]
        );
    
        // Debug the serialized output
        //dd($toolAvailabilities); // 
        //dd($toolAvailabilitiesJSON);
        
        // Prepare variables for rendering
        $vars = ['toolAvailabilitiesJSON' => $toolAvailabilitiesJSON];
        return $this->render('calendar_test/display_tool_availabilities.html.twig', $vars);
    }

    #[Route ('tool/display/all',  name: 'tool_display')]
    public function toolDisplayAll(ManagerRegistry $doctrine){

        $reptools = $doctrine->getRepository(Tool::class);
        $tools = $reptools->findAll();
        $vars = ['tools' => $tools];
        return $this->render('calendar_test/tool_display_all.html.twig', $vars);
    }

    #[Route('/tool/single/{tool_id}', name: 'tool_display_single')]
    public function toolDisplaySingle(ManagerRegistry $doctrine, Request $request, $tool_id): Response
    {
        $request->isMethod('POST');
        
        // Grab tool from the DB
        $repTools = $doctrine->getRepository(Tool::class);
        $tool = $repTools->find($tool_id);

        // Check if the tool exists
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }
        $vars = ['tool' => $tool];
        // Render the template with the tool data
        return $this->render('calendar_test/tool_display_single.html.twig', $vars);

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

            // If the form is submitted and valid, save the borrow tool request
            if ($form->isSubmitted() && $form->isValid()) {
                
                // Get the selected ToolAvailability from the form
                $toolAvailability = $borrowTool->getToolAvailability();

                // Automatically set the start and end dates based on the selected availability
                if ($toolAvailability) {
                    $borrowTool->setStartDate($toolAvailability->getStart());
                    $borrowTool->setEndDate($toolAvailability->getEnd());
                }

                $entityManager = $doctrine->getManager();
                $entityManager->persist($borrowTool);
                $entityManager->flush();

                // Redirect to some success or tool detail page
                return $this->redirectToRoute('tool_display_single', ['tool_id' => $tool_id]);
            }

            // Render the form and tool information
            return $this->render('calendar_test/tool_borrow.html.twig', [
                'form' => $form->createView(),
                'tool' => $tool,
            ]);
        }
    
    
}
