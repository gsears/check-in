<?php

/*
CourseSummaryPageController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Form\Type\RiskSettingsType;
use App\Provider\DateTimeProvider;
use App\Repository\LabRepository;
use App\Service\BreadcrumbBuilder;
use App\Repository\EnrolmentRepository;
use App\Security\Voter\CourseInstanceVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CourseSummaryPageController extends AbstractCourseController
{
    const ROUTE = 'course_instance_summary';

    /**
     * @Route("/courses/{courseId}/{instanceIndex}", name=CourseSummaryPageController::ROUTE)
     */
    public function courseSummaryPage(
        Request $request,
        $instanceIndex,
        $courseId,
        LabRepository $labRepo,
        EnrolmentRepository $enrolmentRepo,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {

        $courseInstance = $this->fetchCourseInstance($instanceIndex, $courseId);

        // Only member instructors can access
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // Create risk settings form
        $riskSettingsForm = $this->createForm(RiskSettingsType::class, $courseInstance);
        $riskSettingsForm->handleRequest($request);

        // If a POST request for the form has been made
        if ($riskSettingsForm->isSubmitted() && $riskSettingsForm->isValid()) {

            // Risk thresholds have been updated
            $updatedCourseInstance = $riskSettingsForm->getData();
            $this->entityManager->persist($updatedCourseInstance);
            $this->entityManager->flush();

            // Force rerender of page to update tables
            return $this->redirect($request->getUri());
        }

        // Build breadcrumbs
        $breadcrumbs = $breadcrumbBuilder
            ->addRoute('Courses', CoursesPageController::ROUTE)
            ->add(strval($courseInstance))
            ->build();

        return $this->render('course/course_summary.html.twig', [
            'courseInstance' => $courseInstance,
            'enrolmentRisks' => $enrolmentRepo->findEnrolmentRisksByCourseInstance($courseInstance, false),
            'labs' => $labRepo->findBy(['courseInstance' => $courseInstance], ['startDateTime' => 'DESC']),
            'currentDate' => (new DateTimeProvider)->getCurrentDateTime(),
            'riskSettingsForm' => $riskSettingsForm->createView(),
            'breadcrumbArray' => $breadcrumbs,
        ]);
    }
}
