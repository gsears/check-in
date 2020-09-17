<?php

/*
StudentSummaryPageController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Entity\Enrolment;
use App\Entity\LabResponse;
use App\Form\Type\RiskFlagType;
use App\Repository\LabRepository;
use App\Service\BreadcrumbBuilder;
use App\Repository\EnrolmentRepository;
use App\Repository\LabResponseRepository;
use App\Security\Voter\CourseInstanceVoter;
use App\Security\Voter\StudentVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StudentSummaryPageController extends AbstractCourseController
{
    const ROUTE = 'student_summary';

    /**
     * @Route("/courses/{courseId}/{instanceIndex}/{studentId}",
     * name=StudentSummaryPageController::ROUTE)
     */
    public function viewCourseStudentSummary(
        Request $request,
        $instanceIndex,
        $courseId,
        $studentId,
        LabRepository $labRepo,
        LabResponseRepository $labResponseRepo,
        EnrolmentRepository $enrolmentRepo,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {

        $courseInstance = $this->fetchCourseInstance($instanceIndex, $courseId);
        $student = $this->fetchStudent($studentId);

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if instructor on that course or the same student
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        $completedLabResponses = $labResponseRepo->findCompletedByCourseInstanceAndStudent($courseInstance, $student);

        // Get risks for completed lab responses
        $completedLabResponseRisks = array_map(function (LabResponse $labResponse) use ($labResponseRepo) {
            return $labResponseRepo->getLabResponseRisk($labResponse);
        }, $completedLabResponses);

        // Generate risk form
        $enrolment = $enrolmentRepo->findOneBy([
            'student' => $student,
            'courseInstance' => $courseInstance
        ]);

        $user = $this->getUser();

        $flagForm = $this->createForm(RiskFlagType::class, $enrolment, [
            RiskFlagType::USER_ROLES => $user->getRoles()
        ]);

        $flagForm->handleRequest($request);

        // Handle POST
        if ($flagForm->isSubmitted() && $flagForm->isValid()) {

            $this->handleSetRiskFlag($flagForm, $user);
            $this->handleRemoveRiskFlag($flagForm);

            // Redirect to self to update page
            return $this->redirectToRoute(self::ROUTE, [
                'courseId' => $courseId,
                'instanceIndex' => $instanceIndex,
                'studentId' => $studentId
            ]);
        }

        // Create breadcrumbs
        $breadcrumbBuilder->addRoute(
            'Courses',
            CoursesPageController::ROUTE
        );

        if ($user->isStudent()) {
            $breadcrumbBuilder
                ->add(strval($courseInstance));
        } else {
            $breadcrumbBuilder
                ->addRoute(
                    strval($courseInstance),
                    CourseSummaryPageController::ROUTE,
                    [
                        'courseId' => $courseId,
                        'instanceIndex' => $instanceIndex
                    ]
                );
        }

        $breadcrumbBuilder->add(strval($student));

        return $this->render('course/student_summary.html.twig', [
            'user' => $this->getUser(),
            'courseInstance' => $courseInstance,
            'pendingLabs' => $labRepo->findPendingSurveysByCourseInstanceAndStudent($courseInstance, $student),
            'completedLabResponseRisks' => $completedLabResponseRisks,
            'student' => $student,
            'enrolment' => $enrolment,
            'breadcrumbArray' => $breadcrumbBuilder->build(),
            'flagForm' => $flagForm->createView()
        ]);
    }

    private function handleSetRiskFlag($flagForm, $user)
    {
        try {
            $manualFlagSubmit = $flagForm->get(RiskFlagType::MANUAL_FLAG_BUTTON);
        } catch (\OutOfBoundsException $e) {
            // Set button not present
            return false;
        }

        if (!$manualFlagSubmit->isClicked()) {
            return false;
        }

        if ($user->isStudent()) {
            $riskFlag = Enrolment::FLAG_BY_STUDENT;
        } else if ($user->isInstructor()) {
            $riskFlag = Enrolment::FLAG_BY_INSTRUCTOR;
        } else {
            throw new \LogicException("Invalid user type setting flag");
        }

        $enrolment = $flagForm->getData();

        // Update the enrolment with the risk flag and the description.
        $enrolment->setRiskFlag(
            $riskFlag,
            $flagForm->get(RiskFlagType::DESCRIPTION_INPUT)->getData()
        );

        $this->entityManager->flush();

        return true;
    }

    private function handleRemoveRiskFlag($flagForm)
    {
        try {
            $removeFlagSubmit = $flagForm->get(RiskFlagType::REMOVE_FLAG_BUTTON);
        } catch (\OutOfBoundsException $e) {
            // Remove button not present
            return false;
        }

        if (!$removeFlagSubmit->isClicked()) {
            return false;
        }

        $enrolment = $flagForm->getData();
        $enrolment->removeRiskFlag();
        $this->entityManager->flush();

        return true;
    }
}
