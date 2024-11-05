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

        // Retrieve the 'keyword' from the query parameters of the request URL 
        $keyword = $request->query->get('keyword');
         // Initialize an empty array for storing the search results.
        $tools = [];
    
        // Check if a keyword was provided in the query parameters.
        if ($keyword) {
            // Perform search directly if the keyword exists
            $tools = $em->getRepository(Tool::class)->createQueryBuilder('t')
                ->where('t.name LIKE :keyword OR t.description LIKE :keyword')
                ->setParameter('keyword', '%' . $keyword . '%')
                ->getQuery()
                ->getResult();
        }
    
    
        return $this->render('search_tool/search.html.twig', [
            'tools' => $tools,
        ]);
    }
}
