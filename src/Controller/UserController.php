<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BorrowToolRepository;
use App\Repository\ToolRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    

    
    #[Route('/user/profile/{user_id}', name: 'user_profile')]
    public function userProfile($user_id, Request $request, ManagerRegistry $doctrine, ToolRepository $toolRepository, BorrowToolRepository $borrowToolRepository): Response
    {

        $user = $doctrine->getRepository(User::class)->find($user_id);
        $tools = $user->getToolsOwned();
        // repository method to get the count
        $borrowedToolsCount = $borrowToolRepository->countBorrowedToolsByBorrower($user);
        $lentToolsCount = $borrowToolRepository->countBorrowedToolsByOwner($user);
        $toolsOwnedByOwnerCount = $toolRepository->countToolsOwnedByOwner($user);
        $freeToolsOwnedByOwnerCount = $toolRepository->countFreeToolsOwnedByOwner($user);


        $reviews = $user->getReviewsReceived();
        $vars = [
            'user' => $user,
            'userId' => $user_id,
            'tools' => $tools,
            'reviews' => $reviews,
            'borrowedToolsCount' => $borrowedToolsCount,
            'toolsOwnedByOwnerCount' => $toolsOwnedByOwnerCount,
            'freeToolsOwnedByOwnerCount' => $freeToolsOwnedByOwnerCount,
            'lentToolsCount' => $lentToolsCount

        ];

        //dd($user);
        //dd($tools);
        //dd($reviews);
        
        return $this->render('user/profile.html.twig', $vars);
    }

    #[Route('/user/self/profile', name: 'my_profile')]
    public function myProfile(ToolRepository $toolRepository, BorrowToolRepository $borrowToolRepository): Response
    {

        $user = $this->getUser();
        //dd($user);

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

        //dd($user);
        //dd($tools);
        //dd($reviews);
        
        return $this->render('user/profile-self.html.twig', $vars);
    }
}
