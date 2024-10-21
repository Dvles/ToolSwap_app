<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\ToolAvailability;
use App\Repository\ToolAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ToolAvailabilityController extends AbstractController
{


    #[Route('tool/add/availability/{tool_id}', name: 'tool_add_availability')]
    public function addToolAvailability(Request $request, EntityManagerInterface $em, $tool_id)
    {
        // Find the tool using the tool_id
        $tool = $em->getRepository(Tool::class)->find($tool_id);

        // Check if the tool exists
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found.');
        }

        //dd($tool);


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
                $toolAvailability->setBackgroundColor($availabilityData['backgroundColor'] ?? '#ffb775');
                $toolAvailability->setBorderColor($availabilityData['borderColor'] ?? '#ffb775');
                $toolAvailability->setTextColor($availabilityData['textColor'] ?? '#000000');


                // Persist the new ToolAvailability
                $em->persist($toolAvailability);
            }


            $em->flush();
            $createdIds[] = $toolAvailability->getId(); // Collect created IDs for UI

            // Return a response (you can return the new availability's ID or just a success message)
            return $this->json(['id' => $toolAvailability->getId()], Response::HTTP_CREATED);
        }

        return $this->render('tool_availability/tool_add_availability.html.twig', [
            'tool' => $tool // Pass the tool if you want to display it in the view
        ]);
    }


    #[Route('tool/delete/availability/', name: "tool_availability_delete")]
    public function deleteToolAvailability(Request $req, ManagerRegistry $doctrine): Response
    {

        $toolAvailabilityObject = json_decode($req->getContent());
        $idDelete = $toolAvailabilityObject->id;


        $em = $doctrine->getManager();
        $rep = $em->getRepository(ToolAvailability::class);
        $toolAvailability = $rep->find($idDelete);
        $em->remove($toolAvailability);
        $em->flush();



        return new Response("Availability deleted", 200);
    }

    #[Route('tool/update/availability/{tool_id}', name: 'tool_availability_update')]
    public function updateToolAvailability(
        SerializerInterface $serializer,
        ManagerRegistry $doctrine,
        $tool_id
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute("app_login");
        }

        // Fetch the tool and check ownership
        $tool = $doctrine->getRepository(Tool::class)->find($tool_id);
        if (!$tool || $tool->getOwner() !== $user) {
            throw $this->createNotFoundException('Tool not found or you are not the owner.');
        }

        // Get all availabilities (not just available ones)
        $toolAvailabilities = $tool->getToolAvailabilities();

        if ($toolAvailabilities instanceof PersistentCollection && !$toolAvailabilities->isInitialized()) {
            $toolAvailabilities->initialize();
        }

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
            // UX -> Optionally inform the user that no availabilities are found
            // UX -> Add a flash message here if needed
            return $this->redirectToRoute("app_login");
        }

        //dd($availableToolAvailabilities);

        // Serealizing data
        $toolAvailabilitiesJSON = $serializer->serialize(
            array_values($availableToolAvailabilities), // Convert associative array to indexed array
            'json',
            [AbstractNormalizer::GROUPS => ['tool:read']]
        );

        //dd($toolAvailabilitiesJSON);

        $vars = [
            'toolAvailabilitiesJSON' => $toolAvailabilitiesJSON,
            'tool' => $tool,
            'tool_id' => $tool_id
        ];


        return $this->render('tool_availability/tool_update_availability.html.twig', $vars);
    }

    #[Route('tool/update/availability/{tool_id}/confirm', name: 'tool_availability_update_confirm')]
    public function confirmToolAvailability($tool_id, Request $request, EntityManagerInterface $em, ToolAvailabilityRepository $repToolAvailability)
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
    
            // Loop through deleted availabilities
            if (isset($content['availabilities']['delete'])) {
                foreach ($content['availabilities']['delete'] as $deletedToolAvailability) {
                    // Check if required keys exist
                    if (!isset($deletedToolAvailability['start'], $deletedToolAvailability['end'], $deletedToolAvailability['toolId'], $deletedToolAvailability['id'])) {
                        return new JsonResponse(['error' => 'Missing required fields'], 400);
                    }

                    // STEP 1 - Delete toolAvailabilities present in $content
                    $toolAvailabilityToDelete = $repToolAvailability->find($deletedToolAvailability['id']);
                    if ($toolAvailabilityToDelete) {
                        $toolAvailabilityToDelete->setIsAvailable(false); 
                        $em->persist($toolAvailabilityToDelete); 
                    }
                }
            }
    
    
            // Save changes
            $em->flush();
    
            return $this->redirectToRoute('tool_availability_success', ['tool_id' => $tool_id]);
    
        } catch (\Exception $e) {
            error_log($e->getMessage()); // Log any exceptions
            error_log($e->getTraceAsString()); // Log the stack trace for more information
            return new JsonResponse(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
    

    #[Route('tool/update/availability/{tool_id}/success', name: 'tool_availability_success')]
    public function toolUpdateAvailabilitySuccess($tool_id)
    {
        return $this->render('tool_availability/tool_update_availability_success.html.twig', [
            'tool_id' => $tool_id,
        ]);
    }
}
