<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\User;
use App\Form\ToolUploadType;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ToolController extends AbstractController
{


    #[Route('tool/upload', name: 'tool_upload')] 
    public function toolUpload(Request $request, EntityManagerInterface $em)
    {
        $tool = new Tool();
        $form = $this->createForm(ToolUploadType::class, $tool);
        
        $form->handleRequest($request);
    
        // Handle form submission
        if ($form->isSubmitted()) {
            // Check if the user is logged in
            $user = $this->getUser();
            if (!$user) {
                // Handle the case where the user is not logged in
                throw $this->createAccessDeniedException('You must be logged in to add a tool.');
            }
    
            if (!$form->isValid()) {
                $errors = $form->getErrors(true); 
                foreach ($errors as $error) {
                    echo 'Error: ' . $error->getMessage() . "<br>";
                }
            } else {
                // Set the current user as the owner
                $tool->setOwner($user);
                $em->persist($tool);
                $em->flush();
    
                // Redirect to the tool availability page, passing the tool ID
                return $this->redirectToRoute('tool_add_availability', ['tool_id' => $tool->getId()]);
            }
        }
    
        // Pass form and tool to the view
        return $this->render('tool/tool_add.html.twig', [
            'form' => $form->createView(),
            'tool' => $tool
        ]);
    }
    

    #[Route('/display/tool/calendar', name: 'display_tool_calendar')]
    public function afficherCalendrierUtilisateur(EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        // check if user is connected or redirected for now
        $user = $this->getUser(); 
        if (is_null($user)) {
            return $this->redirectToRoute("app_login");
        }

        // Fetching the user with associated data
        $userFromDb = $em->getRepository(User::class)->find($user->getId());

        // Validate that the user exists and is valid
        if (!$userFromDb) {
            throw $this->createNotFoundException('User not found.');
        }

        //dd($userFromDb); 
    
        // Get tool availabilities for the user
        $toolAvailabilities = $userFromDb->getToolAvailabilities();

        // Force initialization of the PersistentCollection if not already initialized
        if ($toolAvailabilities instanceof PersistentCollection && !$toolAvailabilities->isInitialized()) {
            $toolAvailabilities->initialize();
        }

        if ($toolAvailabilities->isEmpty()) {
            // Optionally , later add a message to inform user no availabilities found
            return $this->redirectToRoute("app_login"); 
        }
        
    
        // Serialize the availabilities for the JSON response
        $toolAvailabilitiesJSON = $serializer->serialize(
            $toolAvailabilities, 
            'json', 
            [AbstractNormalizer::GROUPS => ['tool:read']]
        );
    
        // Debug the serialized output
        //dd($toolAvailabilities); // 
        //dd($toolAvailabilitiesJSON);
        
        // Prepare variables for rendering
        $vars = ['toolAvailabilitiesJSON' => $toolAvailabilitiesJSON];
        return $this->render('tool/tool_display_availabilities.html.twig', $vars);
    }

    #[Route ('tool/display/all',  name: 'tool_display_all')]
    public function toolDisplayAll(ManagerRegistry $doctrine){

        $reptools = $doctrine->getRepository(Tool::class);
        $tools = $reptools->findAll();
        $vars = ['tools' => $tools];
        return $this->render('tool/tool_display_all.html.twig', $vars);
    }

    #[Route('/tool/single/{tool_id}', name: 'tool_display_single')]
    public function toolDisplaySingle(ManagerRegistry $doctrine, Request $request, $tool_id): Response
    {
        $request->isMethod('POST');
        
        // Grab tool from the DB
        $repTools = $doctrine->getRepository(Tool::class);
        $tool = $repTools->find($tool_id);

        // Check if the tool exists
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }
        $vars = ['tool' => $tool];
        // Render the template with the tool data
        return $this->render('tool/tool_display_single.html.twig', $vars);

    }

    // modify tool controller TBD

   
    
    
}

