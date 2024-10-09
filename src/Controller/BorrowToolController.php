<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\BorrowTool;
use App\Enum\ToolStatusEnum;
use App\Form\BorrowToolType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BorrowToolController extends AbstractController
{
    #[Route('/borrow/tool', name: 'app_borrow_tool')]
    public function index(): Response
    {
        return $this->render('borrow_tool/index.html.twig', [
            'controller_name' => 'BorrowToolController',
        ]);
    }

    #[Route('/tool/single/{tool_id}/borrow', name: 'tool_borrow')]
        public function toolBorrow(ManagerRegistry $doctrine, Request $request, $tool_id): Response
        {
            // Redirect if the user is not logged in
            $user = $this->getUser(); 
            if (!$user) {
                return $this->redirectToRoute("app_login");
            }

            // Fetch the specific tool from the database using the tool ID
            $tool = $doctrine->getRepository(Tool::class)->find($tool_id);
            if (!$tool) {
                throw $this->createNotFoundException('Tool not found');
            }

            // Create a new BorrowTool entity and pre-fill user and tool
            $borrowTool = new BorrowTool();
            
            // Set default values
            $borrowTool->setUserBorrower($user); // Set the current user as the borrower
            $borrowTool->setToolBeingBorrowed($tool); // Set the current tool being borrowed
            $borrowTool->setStatus(ToolStatusEnum::PENDING); // Set default status to "pending"

            // Create the form and handle the request
            $form = $this->createForm(BorrowToolType::class, $borrowTool, [
                'tool' => $tool, // Pass the current tool being borrowed
            ]);
            $form->handleRequest($request);

            // If the form is submitted and valid, save the borrow tool request
            if ($form->isSubmitted() && $form->isValid()) {
                
                // Get the selected ToolAvailability from the form
                $toolAvailability = $borrowTool->getToolAvailability();

                // Automatically set the start and end dates based on the selected availability
                if ($toolAvailability) {
                    $borrowTool->setStartDate($toolAvailability->getStart());
                    $borrowTool->setEndDate($toolAvailability->getEnd());
                }

                // Set the ToolAvailability to be unavailable (-> so it's filtered out from BorrowTool dropdown of ToolAvailabilities)
                $toolAvailability->setIsAvailable(false); 
                $doctrine->getManager()->persist($toolAvailability);
                
                $em = $doctrine->getManager();
                $em->persist($borrowTool);
                $em->flush();


                // Redirect to some success or tool detail page
                return $this->redirectToRoute('tool_display_single', ['tool_id' => $tool_id]);
            }

            // Render the form and tool information
            return $this->render('borrow_tool/tool_borrow.html.twig', [
                'form' => $form->createView(),
                'tool' => $tool,
            ]);
        }
    
    
}


