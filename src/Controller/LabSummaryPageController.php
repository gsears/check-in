<?php

/*
LabSummaryPageController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Form\Type\LabDangerZoneType;
use App\Repository\LabRepository;
use App\Service\BreadcrumbBuilder;
use App\Security\Voter\CourseInstanceVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LabSummaryPageController extends AbstractCourseController
{
    const ROUTE = 'lab_summary';

    /**
     * @Route("/courses/{courseId}/{instanceIndex}/lab/{labSlug}",
     * name=LabSummaryPageController::ROUTE)
     */

    public function viewLabSummary(
        Request $request,
        $instanceIndex,
        $courseId,
        $labSlug,
        LabRepository $labRepo,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {

        $courseInstance = $this->fetchCourseInstance($instanceIndex, $courseId);
        $lab = $this->fetchLab($labSlug, $courseInstance);

        // Only people who can edit this course instance are allowed to view (i.e. instructors on the course)
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // Danger Zone Form
        $dangerZoneForm = $this->createForm(LabDangerZoneType::class, $lab);
        $dangerZoneForm->handleRequest($request);

        // Handle POST
        if ($dangerZoneForm->isSubmitted() && $dangerZoneForm->isValid()) {
            $updatedLab = $dangerZoneForm->getData();
            $this->entityManager->persist($updatedLab);
            $this->entityManager->flush();
        }

        // Breadcrumbs
        $breadcrumbBuilder
            ->addRoute('Courses', CoursesPageController::ROUTE)
            ->addRoute(strval($courseInstance), CourseSummaryPageController::ROUTE, [
                'courseId' => $courseId,
                'instanceIndex' => $instanceIndex
            ])
            ->add($lab->getName());

        return $this->render('lab/lab_summary.html.twig', [
            'courseInstance' => $courseInstance,
            'lab' => $lab,
            'form' => $dangerZoneForm->createView(),
            'labResponseRisks' => $labRepo->getLabResponseRisks($lab),
            'breadcrumbArray' => $breadcrumbBuilder->build()
        ]);
    }
}
