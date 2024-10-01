<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

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

        
        $toolAvailability = $user->getToolAvailabilities();
        dd($toolAvailability);
        // pour debugger, vous pouvez faire de dumps. Attention: un dd($evenements)
        // dump ($evenements);
        // dump($evenements[0]);
        // dd($evenements[1]); // etc...


        // Serialiser = Normaliser (passer objet ou array d'objets à array) et Encoder (passer array à JSON)
        // https://symfony.com/doc/current/components/serializer.html (regardez le dessin)
        // Si vous avez de problèmes de CIRCULAR REFERENCE, utilisez IGNORED_ATTRIBUTS pour ne pas 
        // serialiser les propriétés qui constituent une rélation (ex: serialiser Livre sans serialiser les Exemplaires)
        // $evenementsJSON = $serializer->serialize($evenements, 'json',[AbstractNormalizer::IGNORED_ATTRIBUTES => ['utilisateur']]);
        // $evenementsJSON = $serializer->serialize($evenements, 'json',[AbstractNormalizer::ATTRIBUTES => ['start','title']]);
        $evenementsJSON = $serializer->serialize($evenements, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['utilisateur']]);
        $vars = ['evenementsJSON' => $evenementsJSON];
        return $this->render('full_calendar_evenements/afficher_calendrier_utilisateur.html.twig', $vars);
    }
}
