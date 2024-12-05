<?php

namespace App\Controller;

use App\Entity\Tool;
use App\Entity\ToolCategory;
use App\Entity\ToolReview;
use App\Entity\User;
use App\Form\ToolFilterType;
use App\Form\ToolUploadType;
use App\Repository\BorrowToolRepository;
use App\Repository\ToolAvailabilityRepository;
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

        // !!HACK!! Replace all images until image upload service exists



        $form->handleRequest($request);

        // Handle form submission
        if ($form->isSubmitted()) {
            // Check if the user is logged in
            $user = $this->getUser();
            if (!$user) {
                // Handle the case where the user is not logged in
                throw $this->createAccessDeniedException('You must be logged in to add a tool.');
            }

            $tool->setImageTool('https://via.placeholder.com/500x500'); // Placeholder image URL


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

                $vars = [
                    'tool_id' => $tool->getId(),
                    'tool' => $tool

                ];

                // Redirect to the tool availability page, passing the tool ID
                return $this->redirectToRoute('tool_add_availability',$vars);
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

        // Since communities are strings (not entities with IDs), we use names as both keys and values.
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

        // Initialize tools variable to hold all tools initially
        $tools = $toolRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            // Get form data
            $data = $form->getData();

            // Filter tools based on form data, using community name directly
            $tools = $toolRepository->findByFilters(
                $data['isFree'],
                $data['category'],
                $data['community'] // Pass community name directly as it's a string
            );
            // dd($tools);
        }

        $vars = [
            'tools' => $tools,
            'form' => $form->createView()
        ];

        return $this->render('tool/tool_display_all.html.twig', $vars);
    }






    #[Route('/tool/single/{tool_id}', name: 'tool_display_single')]
    public function toolDisplaySingle(
        ManagerRegistry $doctrine,
        Request $request,
        $tool_id,
        ToolRepository $repTools,
        EntityManagerInterface $em,
        BorrowToolRepository $borrowToolRep,
        ToolAvailabilityRepository $toolAvailabilityRep
    ): Response {
        // Check if the user is logged in
        $user = $this->getUser();

        // Grab the tool from the DB
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


        // Initialize flags
        $activeBorrowTool = false; // Default is no active borrow tools
        $pastBorrowTool = false;   // Default is no past borrow tools

        // Ensure both the end date and the current date are in the same timezone
        $currentDate = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));

        if ($tool->getBorrowTools()->count() > 0) {
            // Loop through all borrow tools
            foreach ($tool->getBorrowTools() as $borrowTool) {
                // Get the end date of the borrow tool
                $borrowEndDate = $borrowTool->getEndDate();

                // Normalize the borrow tool's end date to the same timezone
                $borrowEndDate->setTimezone(new \DateTimeZone('Europe/Berlin'));

                // Debugging: Show the dates after normalizing
                //dd($borrowEndDate, $currentDate);

                if ($borrowEndDate > $currentDate) {
                    $activeBorrowTool = true; // Found at least one active borrow tool
                    break; 
                } else {
                    $pastBorrowTool = true; 
                }
            }
        }

        //dd($activeBorrowTool, $pastBorrowTool);


        // No deletion, so continue rendering the single tool
        $vars = [
            'tool' => $tool,
            'isOwner' => $isOwner,
            'toolReviews' => $toolReviewData,
            'activeBorrowTool' => $activeBorrowTool,
            'pastBorrowTool' => $pastBorrowTool,
        ];

        // Render the template with the tool data
        return $this->render('tool/tool_display_single.html.twig', $vars);
    }



    #[Route('/tool/display/user', name: 'tool_display_user')]
    public function toolDisplayUser(ToolRepository $repTools)
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }

        // Fetch only active tools of the logged-in user
        $userTools = $repTools->findActiveTools($user);


        // Initialize a flag for active borrowings
        $activeBorrowToolsIds = [];
        $activeBorrowTools = [];

        // Initialize empty borrowTools 
        $borrowTools = [];
        $tool_id = 0;
        $pastBorrowToolsIds = [];
        $activeBorrowToolsIds = [];


        // Check if any tool has active borrowings
        foreach ($userTools as $tool) {
            $borrowTools = $tool->getBorrowTools();
            if ($borrowTools->count() > 0) {
                foreach ($borrowTools as $borrowTool) {
                    // Store ToolID and Tool
                    if ($borrowTool->getEndDate() > new \DateTime()) {
                        $activeBorrowToolsIds[] = $tool->getId();
                        $activeBorrowTools[] = $tool;
                    } else {
                        $pastBorrowToolsIds[] = $tool->getId();
                    }
                }
            }
        }

        



        //dd($activeBorrowToolsIds);
        //dd($activeBorrowTools);

        // Pass tools and the activeBorrowTool flag to the template
        $vars = [
            'tools' => $userTools,
            'activeBorrowToolsIds' => $activeBorrowToolsIds,
            'activeBorrowTools' => $activeBorrowTools,
            'borrowTools' => $borrowTools,
            'tool_id' => $tool_id,
            'pastBorrowToolsIds' => $pastBorrowToolsIds,
            'userToolsCount' => $repTools->countToolsOwnedByOwner($user)

        ];

        return $this->render('tool/tool_display_user.html.twig', $vars);
    }


    #[Route('/tool/single/{tool_id}/delete/simple', name: 'tool_delete_simple')]
    public function toolDeleteSimple(Request $request, ToolRepository $repTools, EntityManagerInterface $em)
    {

        $tool_id = $request->get('tool_id');
        $tool = $repTools->find($tool_id);
        //dd($tool);

        if (!$tool) {
            throw $this->createNotFoundException('No tool found');
        }

        $em->remove($tool);
        $em->flush();

        return $this->redirectToRoute('tool_display_user');
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

    #[Route('/tool/single/{tool_id}/delete/', name: 'tool_delete')]
    public function toolDelete(
        Request $request,
        ToolRepository $repTools,
        EntityManagerInterface $em,
        BorrowToolRepository $borrowToolRep,
        ToolAvailabilityRepository $toolAvailabilityRep
    ) {
        $tool_id = $request->get('tool_id');
        $tool = $repTools->find($tool_id);
        $tools = $repTools->findAll();

        if (!$tool) {
            throw $this->createNotFoundException('No tool found');
        }

        // Get the borrowings for the tool
        $borrowTools = $borrowToolRep->findBy(['toolBeingBorrowed' => $tool]);

        // Initialize activeBorrowTool flag
        $activeBorrowTool = false;

        // Check if there are any active borrowings
        foreach ($borrowTools as $borrowTool) {
            // If any borrowing has an active date (ToolStatus != completed or returned), set the flag
            if ($borrowTool->getEndDate() > new \DateTime()) {
                $activeBorrowTool = true;
                break;
            }
        }

        // If there are active borrowings, handle availability
        if ($activeBorrowTool) {
            $toolAvailabilities = $toolAvailabilityRep->findBy(['tool' => $tool]);
            foreach ($toolAvailabilities as $availability) {
                $availability->setIsAvailable(false);
                $em->persist($availability);
            }

            // Flush to ensure changes are saved
            $em->flush();

            // Prepare variables for the template
            $vars = [
                'tool' => $tool,
                'tools' => $tools,
                'borrowTools' => $borrowTools,
                'activeBorrowTool' => $activeBorrowTool, // Pass the flag as true
            ];

            // Render the template with the modal
            return $this->render('tool/tool_display_user.html.twig', $vars);
        }

        // If there are borrowTools but no active borrowings, mark tool as disabled
        if ($borrowTools) {
            $toolAvailabilities = $toolAvailabilityRep->findBy(['tool' => $tool]);
            foreach ($toolAvailabilities as $availability) {
                $availability->setIsAvailable(false);
                $em->persist($availability);
            }

            $tool->setIsDisabled(true); // Ensure the tool is marked as disabled

            $em->flush();

            return $this->redirectToRoute('tool_display_user');
        } else {
            // If no borrowings, safely delete the tool
            $em->remove($tool);
            $em->flush();

            return $this->redirectToRoute('tool_display_user');
        }
    }

    // reusable action for future delete buttons in application
    // In the same controller

    private function handleToolDeletion(
        $tool_id,
        ToolRepository $repTools,
        EntityManagerInterface $em,
        BorrowToolRepository $borrowToolRep,
        ToolAvailabilityRepository $toolAvailabilityRep
    ) {
        $tool = $repTools->find($tool_id);
        if (!$tool) {
            throw $this->createNotFoundException('No tool found');
        }

        // Get the borrowings for the tool
        $borrowTools = $borrowToolRep->findBy(['toolBeingBorrowed' => $tool]);

        // Initialize activeBorrowTool flag
        $activeBorrowTool = false;

        // Check if there are any active borrowings
        foreach ($borrowTools as $borrowTool) {
            if ($borrowTool->getEndDate() > new \DateTime()) {
                $activeBorrowTool = true;
                break;
            }
        }

        // If there are active borrowings, handle availability
        if ($activeBorrowTool) {
            $toolAvailabilities = $toolAvailabilityRep->findBy(['tool' => $tool]);
            foreach ($toolAvailabilities as $availability) {
                $availability->setIsAvailable(false);
                $em->persist($availability);
            }
            $em->flush();

            return [
                'tool' => $tool,
                'borrowTools' => $borrowTools,
                'activeBorrowTool' => $activeBorrowTool
            ];
        }

        // If there are borrowTools but no active borrowings, mark tool as disabled
        if ($borrowTools) {
            $toolAvailabilities = $toolAvailabilityRep->findBy(['tool' => $tool]);
            foreach ($toolAvailabilities as $availability) {
                $availability->setIsAvailable(false);
                $em->persist($availability);
            }

            $tool->setIsDisabled(true); // Mark tool as disabled
            $em->flush();

            return ['tool' => $tool];
        } else {
            // If no borrowings, safely delete the tool
            $em->remove($tool);
            $em->flush();

            return null;
        }
    }

    #[Route('/tool/single/{tool_id}/disable/', name: 'tool_disable')]
    public function toolDisable(Request $request, ToolRepository $repTools, EntityManagerInterface $em)
    {
        $tool_id = $request->get('tool_id');
        $tool = $repTools->find($tool_id);
        $tools = $repTools->findAll();

        if (!$tool) {
            throw $this->createNotFoundException('No tool found');
        }

        $tool->setIsDisabled(true);
        $em->flush();

        return $this->redirectToRoute('tool_display_user');
    }
}
