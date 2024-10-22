<?php

namespace App\Controller;

use App\Entity\Tool;
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
    public function toolReviewAdd(Request $request)
    {

        $tool_id = $request->get('id');
        dd($tool_id);

        return $this->render('tool_review/index.html.twig', [
            'controller_name' => 'ToolReviewController',
        ]);
    }
}
