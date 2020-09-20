<?php

/*
CoursesPageController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Security\Roles;
use App\Repository\LabRepository;
use App\Service\BreadcrumbBuilder;
use App\Repository\EnrolmentRepository;
use App\Repository\CourseInstanceRepository;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Renders the courses page (the home page).
 */
class CoursesPageController extends AbstractCourseController
{
    const ROUTE = 'course_instances';

    /**
     * @Route("/courses", name=CoursesPageController::ROUTE)
     */
    public function coursesPage(
        CourseInstanceRepository $courseInstanceRepo,
        LabRepository $labRepo,
        EnrolmentRepository $enrolmentRepo,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {
        // Security
        $this->denyAccessUnlessGranted(Roles::LOGGED_IN);

        $user = $this->getUser();
        $breadcrumbs = $breadcrumbBuilder->add('Courses')->build();

        // Render appropriate view for different users
        if ($user->isStudent()) {
            $student = $user->getStudent();

            return $this->render('course/courses_student.html.twig', [
                'enrolments' => $enrolmentRepo->findBy(['student' => $student]),
                'studentId' => $student->getGuid(),
                'recentLabs' => $labRepo->findLatestPendingByStudent($student, 5),
                'breadcrumbArray' => $breadcrumbs,
            ]);
        }

        if ($user->isInstructor()) {
            $instructor = $user->getInstructor();

            return $this->render('course/courses_instructor.html.twig', [
                'courseInstances' => $courseInstanceRepo->findByInstructor($instructor),
                'recentLabs' => $labRepo->findLatestByInstructor($instructor, 5),
                'breadcrumbArray' => $breadcrumbBuilder->build()
            ]);
        }

        // If an unknown user type, simply deny.
        // Code should never reach here.
        $this->createAccessDeniedException("Invalid user role");
    }
}
