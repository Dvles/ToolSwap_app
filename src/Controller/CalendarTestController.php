<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


class CalendarTestController extends AbstractController
{
    #[Route('/calendar/test', name: 'app_calendar_test')]
    public function index(): Response
    {
        return $this->render('calendar_test/index.html.twig', [
            'controller_name' => 'CalendarTestController',
        ]);
    }

    #[Route('/display/tool/calendar', name: 'display_tool_calendar')]
    public function afficherCalendrierUtilisateur(SerializerInterface $serializer): Response
    {
        $user = $this->getUser(); 
        if (is_null($user)) {
            return $this->redirectToRoute("app_login");
        }
    
        // Get tool availabilities for the user
        $toolAvailabilities = $user->getToolAvailabilities();
        
        // Debug: Check if there are any availabilities
        if ($toolAvailabilities->isEmpty()) {
            // Optionally add a message to inform user no availabilities found
            dd($toolAvailabilities);
            
            return $this->redirectToRoute("app_login"); 
        }
        
        // Debugging output to check the availabilities
        // dd($toolAvailabilities); // Check what is being returned
    
        // Serialize the availabilities for the JSON response
        $toolAvailabilitiesJSON = $serializer->serialize(
            $toolAvailabilities, 
            'json', 
            [AbstractNormalizer::GROUPS => ['tool:read']]
        );
    
        // Debug the serialized output
        dd($toolAvailabilitiesJSON); // This should be valid JSON
        
        // Prepare variables for rendering
        $vars = ['toolAvailabilitiesJSON' => $toolAvailabilitiesJSON];
        return $this->render('calendar_test/display_tool_availabilities.html.twig', $vars);
    }
    
    
}
