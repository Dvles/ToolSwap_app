<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BorrowToolRepository;
use App\Repository\ToolRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index( ManagerRegistry $doctrine, ToolRepository $toolRepository, BorrowToolRepository $borrowToolRepository): Response
    {
        $user = $this->getUser();
        if ($user) {

            $tools = $user->getToolsOwned();
            // repository method to get the count
            $borrowedToolsCount = $borrowToolRepository->countBorrowedToolsByBorrower($user);
            $lentToolsCount = $borrowToolRepository->countBorrowedToolsByOwner($user);
            $toolsOwnedByOwnerCount = $toolRepository->countToolsOwnedByOwner($user);
            $freeToolsOwnedByOwnerCount = $toolRepository->countFreeToolsOwnedByOwner($user);
    
    
            $reviews = $user->getReviewsReceived();
            $vars = [
                'user' => $user,
                'tools' => $tools,
                'reviews' => $reviews,
                'borrowedToolsCount' => $borrowedToolsCount,
                'toolsOwnedByOwnerCount' => $toolsOwnedByOwnerCount,
                'freeToolsOwnedByOwnerCount' => $freeToolsOwnedByOwnerCount,
                'lentToolsCount' => $lentToolsCount
    
            ];
            
            return $this->render('homepage/index.html.twig', $vars);

        } else {

            return $this->redirectToRoute('tool_display_all');

        } 
    }

    #[Route('/coming/soon', name: 'coming_soon')]
    public function comingSoon(): Response
    {
        return $this->render('homepage/coming_soon.html.twig');
    }
}
