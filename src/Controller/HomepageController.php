<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig');
    }

    #[Route('/coming/soon', name: 'coming_soon')]
    public function comingSoon(): Response
    {
        return $this->render('homepage/coming_soon.html.twig');
    }
}
