<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\ToolCategory;
use App\Entity\ToolReview;
use App\Entity\User;
use App\Form\ToolFilterType;
use App\Form\ToolUploadType;
use App\Repository\ToolRepository;
use App\Repository\UserRepository;
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
        // check if user is connected or redirected for now - may not be necessaru as only logged in users should have access to url?
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

    // Display and filter method
    #[Route('tool/display/all', name: 'tool_display_all')]
    public function toolDisplayAll(Request $request, EntityManagerInterface $em, UserRepository $userRepository, ToolRepository $toolRepository): Response
    {
        // Fetch categories and communities
        $categories = $em->getRepository(ToolCategory::class)->findAll();
        $communities = $userRepository->findCommunities();

        // Prepare category and community choices as associative arrays
        $categoryChoices = [];
        foreach ($categories as $category) {
            $categoryChoices[$category->getName()] = $category->getId();  // Name as label, ID as value
        }

        $communityChoices = [];
        foreach ($communities as $community) {
            $communityChoices[$community['community']] = $community['community'];  // Community name
        }

        // Create the form and handle request
        $form = $this->createForm(ToolFilterType::class, null, [
            'categories' => $categoryChoices,
            'communities' => $communityChoices
        ]);

        $form->handleRequest($request);

        // Debug: Check if form is submitted and valid
        if ($form->isSubmitted()) {
            // dd($form->getData());  // Show data after form submission
        }

        // Initialize tools variable to hold all tools initially
        $tools = $toolRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            // Get form data
            $data = $form->getData();

            // Debug: Check form data before applying filters
            // dd('Form data:', $data);

            // Filter tools based on form data
            $tools = $toolRepository->findByFilters(
                $data['isFree'],
                $data['category'],
                $data['community']
            );
        }

        $vars = [
            'tools' => $tools,
            'form' => $form->createView()
        ];

        return $this->render('tool/tool_display_all.html.twig', $vars);
    }






    #[Route('/tool/single/{tool_id}', name: 'tool_display_single')]
    public function toolDisplaySingle(ManagerRegistry $doctrine, Request $request, $tool_id): Response
    {
        // Check if the user is logged in
        $user = $this->getUser();

        // Grab the tool from the DB
        $repTools = $doctrine->getRepository(Tool::class);
        $tool = $repTools->find($tool_id);

        // Check if the tool exists
        if (!$tool) {
            throw $this->createNotFoundException('Tool not found');
        }

        // Check if the user is the owner of the tool
        $isOwner = $user && $tool->getOwner() === $user;

        // Initialize the toolReviews collection
        $toolReviews = $tool->getToolReviews();

        // Prepare the tool reviews data to avoid lazy loading and errors
        $toolReviewData = [];
        foreach ($toolReviews as $review) {
            $toolReviewData[] = [
                'id' => $review->getId(),
                'comment' => $review->getComment(),
                'rating' => $review->getRating(),
                'reviewer' => $review->getUserOfReview()->getFirstName(),
                'reviewerId' => $review->getUserOfReview()->getId()
            ];
        }

        $vars = [
            'tool' => $tool,
            'isOwner' => $isOwner,
            'toolReviews' => $toolReviewData
        ];

        // Render the template with the tool data
        return $this->render('tool/tool_display_single.html.twig', $vars);
    }


    #[Route('/tool/display/user', name: 'tool_display_user')]
    public function toolDisplayUser(ManagerRegistry $doctrine)
    {

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }

        // Fetch the EntityManager
        $em = $doctrine->getManager();

        // Fetch the user with their tools using a Doctrine query to ensure the relation is loaded
        $userWithTools = $em->getRepository(User::class)->find($user->getId());

        // Check if tools are fetched
        $userTools = $userWithTools->getToolsOwned();

        // Debugging check for fetched data
        //dd($user->getToolsOwned()->toArray());

        $vars = ['tools' => $userTools];

        return $this->render('tool/tool_display_user.html.twig', $vars);
    }

    #[Route('/tool/single/{tool_id}/delete', name: 'tool_delete')]
    public function toolDelete(Request $request, ToolRepository $repTools, EntityManagerInterface $em)
    {

        $tool_id = $request->get('tool_id');
        $tool = $repTools->find($tool_id);
        //dd($tool);

        if (!$tool) {
            throw $this->createNotFoundException('No tool found');
        }

        $em->remove($tool);
        $em->flush();

        return $this->redirectToRoute('tool_display_all');
    }


    #[Route('/tool/single/{tool_id}/update', name: 'tool_update')]
    public function toolUpdate(Request $request, ToolRepository $repTools, EntityManagerInterface $em)
    {

        $tool_id = $request->get('tool_id');
        $tool = $repTools->find($tool_id);
        //dd($tool);

        if (!$tool) {
            throw $this->createNotFoundException('No tool found');
        }

        $form = $this->createForm(ToolUploadType::class, $tool);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em->flush();
            $vars = ['tool_id' => $tool_id];
            return $this->redirectToRoute('tool_display_single', $vars);
        }

        $vars = ['form' => $form];

        return $this->render('tool/tool_update.html.twig', $vars);
    }
}
