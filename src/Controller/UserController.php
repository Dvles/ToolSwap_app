<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ToolRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    

    
    #[Route('/user/profile/{user_id}', name: 'user_profile')]
    public function userProfile($user_id, Request $request, ManagerRegistry $doctrine, ToolRepository $toolRepository): Response
    {

        $user = $doctrine->getRepository(User::class)->find($user_id);
        $tools = $user->getToolsOwned();
        // repository method to get the count
        $borrowedToolsCount = $toolRepository->countBorrowedToolsByOwner($user);
        // repository method to get the count
        $toolsOwnedByOwnerCount = $toolRepository->countToolsOwnedByOwner($user);
        // repository method to get the count
        $freeToolsOwnedByOwnerCount = $toolRepository->countFreeToolsOwnedByOwner($user);

        $reviews = $user->getReviewsReceived();
        $vars = [
            'user' => $user,
            'tools' => $tools,
            'reviews' => $reviews,
            'borrowedToolsCount' => $borrowedToolsCount,
            'toolsOwnedByOwnerCount' => $toolsOwnedByOwnerCount,
            'freeToolsOwnedByOwnerCount' => $freeToolsOwnedByOwnerCount
        ];

        //dd($user);
        //dd($tools);
        //dd($reviews);
        
        return $this->render('user/profile.html.twig', $vars);
    }
}
