<?php

namespace App\Controller;

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

    #[Route('/tool/review/{tool_id}/add', name: 'tool_review_addy')]
    public function toolReviewAss(Request $request){

        $tool_id = $request->get('id');
        dd($tool_id);

        return $this->render('tool_review/index.html.twig', [
            'controller_name' => 'ToolReviewController',
        ]);


    }



}
