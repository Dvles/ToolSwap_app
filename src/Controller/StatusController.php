<?php

namespace App\Controller;

use App\Entity\BorrowTool;
use App\Enum\ToolStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends AbstractController
{
    #[Route('/status/update/{tool_id}', name: 'status_update')]
    public function statusUpdate(
        int $tool_id, Request $request, EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }

        // Fetch the tool
        $borrowTool = $entityManager->getRepository(BorrowTool::class)->find($tool_id);

        if (!$borrowTool) {
            throw $this->createNotFoundException('Tool not found.');
        }

        //dd($tool_id);

        // Fetch possible statuses
        $statuses = ToolStatusEnum::cases();

        // Handle POST request
        if ($request->isMethod('POST')) {
            $newStatus = $request->request->get('status');

            if ($newStatus && ToolStatusEnum::tryFrom($newStatus)) {
                $borrowTool->setStatus(ToolStatusEnum::from($newStatus));
                $entityManager->persist($borrowTool);
                $entityManager->flush();

                $this->addFlash('success', 'Tool status updated successfully.');

                return $this->redirectToRoute('tool_borrow_display');
            }

            $this->addFlash('error', 'Invalid status selected.');
        }

        // Render the form
        return $this->render('status/update.html.twig', [
            'tool_id' => $tool_id,
            'borrow_tool' => $borrowTool,
            'statuses' => $statuses,
            'currentStatus' => $borrowTool->getStatus(),
        ]);
    }
}
