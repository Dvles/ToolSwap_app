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

        
        $toolAvailabilities = $user->getToolAvailabilities();
        //dd($toolAvailabilities);
        
        $toolAvailabilitiesJSON = $serializer->serialize($evenements, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['user']]);
        $vars = ['toolAvailabilitiesJSON' => $toolAvailabilitiesJSON];
        return $this->render('calendar_test/display_tool_availabilities', $vars);
    }
}
