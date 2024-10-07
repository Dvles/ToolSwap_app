<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\ToolAvailability;
use App\Entity\User;
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
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the current user as the owner
            $tool->setOwner($this->getUser());
            $em->persist($tool);
            $em->flush();

            //dd("it works");
    
            // Redirect to the tool availability page, passing the tool ID
            return $this->redirectToRoute('tool_add_availability', ['tool_id' => $tool->getId()]);
        } else {
        // Retrieve form errors
            $errors = $form->getErrors(true); // true means to get errors from all child forms as well

            foreach ($errors as $error) {
                // Now we are working with each individual FormError
                echo 'Error: ' . $error->getMessage() . "<br>";
            }
            
            // Debugging: Dump the errors for further inspection
            dd($errors); // Dump the error iterator for deeper inspection
        }
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
    
    
}
