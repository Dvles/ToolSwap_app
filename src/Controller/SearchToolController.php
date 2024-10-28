<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Form\SearchToolType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchToolController extends AbstractController
{
    #[Route('/tools/search', name: 'tool_search')]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SearchToolType::class);
        $form->handleRequest($request);
        
        $tools = [];
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $keyword = $data['keyword'];
    
            // Perform search based on the existing properties
            $tools = $em->getRepository(Tool::class)->createQueryBuilder('t')
                ->where('t.name LIKE :keyword OR t.description LIKE :keyword')
                ->setParameter('keyword', '%' . $keyword . '%')
                ->getQuery()
                ->getResult();
        }

        $vars = [
            'form' => $form->createView(),
            'tools' => $tools,
        ];

        return $this->render('search_tool/search.html.twig', $vars);
    }
}
