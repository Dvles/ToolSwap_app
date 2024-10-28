<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\ToolReview;
use App\Form\ToolReviewType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ToolReviewController extends AbstractController
{
    #[Route('/tool/review', name: 'app_tool_review')]
    public function index(): Response
    {
        return $this->render('tool_review/index.html.twig', [
            'controller_name' => 'ToolReviewController',
        ]);
    }

    #[Route('/tool/review/{tool_id}/display', name: 'tool_review_display')]
    public function toolReviewDisplay(ManagerRegistry $doctrine, $tool_id)
    {


        // Grab the tool from the DB
        $repTools = $doctrine->getRepository(Tool::class);
        $tool = $repTools->find($tool_id);

        // Initialize the toolReviews collection
        $toolReviews = $tool->getToolReviews();
    
        // Prepare the tool reviews data to avoid lazy loading and errors
        $toolReviewData = [];
        foreach ($toolReviews as $review) {
            $toolReviewData[] = [
                'id' => $review->getId(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'reviewer' => $review->getUserOfReview()->getFirstName()
            ];
        }

        $vars = [
            'tool' => $tool,
            'toolReviews' => $toolReviewData
        ];

        return $this->render('tool_review/display.html.twig', $vars);
    }

    #[Route('/tool/review/{tool_id}/add', name: 'tool_review_add')]
    public function toolReviewAdd(ManagerRegistry $doctrine, Request $request)
    {
        // Redirect if the user is not logged in
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute("app_login");
        }
    
        // Fetch the specific tool from the database using the tool ID from the request
        $tool_id = $request->get('tool_id');
        $tool = $doctrine->getRepository(Tool::class)->find($tool_id);
    
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }
    
        // Create a new ToolReview object and pre-fill user and tool
        $toolReview = new ToolReview();
        $toolReview->setUserOfReview($user); 
        $toolReview->setToolOfReview($tool);
    
        // Create the form with the ToolReviewType form class
        $form = $this->createForm(ToolReviewType::class, $toolReview);
    
        // Handle the form submission
        $form->handleRequest($request);
    
        // If the form is submitted and valid, save the tool review
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($toolReview);
            $em->flush();
    
            return $this->redirectToRoute('tool_display_single', ['tool_id' => $tool_id]);
        }
    
        // Pass the form, tool & tool_id to the view
        $vars = [
            'form' => $form->createView(),
            'tool' => $tool,
            'tool_id' => $tool_id
        ];
    
        return $this->render('tool_review/review_add.html.twig', $vars);
    }
    
}
