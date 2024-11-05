<?php

namespace App\Controller;

use App\Entity\LenderReview;
use App\Entity\Tool;
use App\Entity\ToolReview;
use App\Entity\User;
use App\Form\LenderReviewType;
use App\Form\ToolReviewType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LenderReviewController extends AbstractController
{


    #[Route('/lender/review/{user_id}/display', name: 'lender_reviews_left_display')]
    public function lenderReviewsLeftDisplay(ManagerRegistry $doctrine, $user_id)
    {


        // Grab the user from the DB
        $repOwners = $doctrine->getRepository(User::class);
        $owner = $repOwners->find($user_id);

        //dd($owner);
        //dd($user_id);

        // Initialize the LenderReviews collection
        $LenderReviews = $owner->getUserReviews();

        ///dd($LenderReviews);
    
        // Prepare the lender reviews data to avoid lazy loading and errors
        $LenderReviewData = [];
        foreach ($LenderReviews as $review) {
            $LenderReviewData[] = [
                'id' => $review->getId(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'reviewer' => $review->getUserOfReview()->getFirstName()
            ];
        }

        $vars = [
            'owner' => $owner,
            'lenderReviews' => $LenderReviewData
        ];

        return $this->render('lender_review/user_reviews_display.html.twig', $vars);
    }

    #[Route('/lender/review/{user_id}/add', name: 'lender_review_add')]
    public function lenderReviewAdd(ManagerRegistry $doctrine, Request $request)
    {
        // Redirect if the user is not logged in
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute("app_login");
        }
    
        // Fetch the specific user_to_be_reviewed from the database using the tool ID from the request
        $user_id = $request->get('user_id');
        $userBeingReviewed = $doctrine->getRepository(User::class)->find($user_id);
    
        if (!$userBeingReviewed) {
            throw $this->createNotFoundException('User not found');
        }
    
        // Create a new LenderReview object and pre-fill user and tool
        $lenderReview = new LenderReview();
        $lenderReview->setUserBeingReviewed($userBeingReviewed ); 
        $lenderReview->setUserLeavingReview($user);
    
        // Create the form with the LenderReviewType form class
        $form = $this->createForm(LenderReviewType::class, $lenderReview);
    
        // Handle the form submission
        $form->handleRequest($request);
    
        // If the form is submitted and valid, save the tool review
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($lenderReview);
            $em->flush();
    
            return $this->redirectToRoute('tool_borrow_lending_display');
        }
    
        // Pass the form, tool & tool_id to the view
        $vars = [
            'form' => $form->createView(),
            'userBeingReviewed' => $userBeingReviewed->getFirstName(),
            'user_id' => $user_id
        ];
    
        return $this->render('lender_review/review_add.html.twig', $vars);
    }
    
}
