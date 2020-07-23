<?php

namespace App\Controller;

use App\Entity\CourseInstance;
use App\Entity\Lab;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Entity\LabXYQuestionResponse;
use App\Entity\Student;
use App\Entity\SurveyQuestionInterface;
use App\Entity\User;
use App\Entity\XYQuestion;
use App\Entity\XYQuestionDangerZone;
use App\Form\Type\LabDangerZoneType;
use App\Form\Type\LabResponseType;
use App\Form\Type\LabXYQuestionResponseType;
use App\Form\Type\LabXYQuestionType;
use App\Form\Type\SurveyQuestionResponseType;
use App\Form\Type\XYCoordinatesType;
use App\Security\Roles;
use App\Security\Voter\CourseInstanceVoter;
use App\Security\Voter\StudentVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/", name="courses")
     */
    public function index()
    {
        $user = $this->getUser();

        if ($user->isStudent()) {
            $courseInstances = $this->getDoctrine()
                ->getRepository(CourseInstance::class)
                ->findByStudent($user->getStudent());

            return $this->render('course/courses_student.html.twig', [
                'courseInstances' => $courseInstances,
                'studentId' => $user->getStudent()->getGuid()
            ]);
        }

        if ($user->isInstructor()) {
            $courseInstances = $this->getDoctrine()
                ->getRepository(CourseInstance::class)
                ->findByInstructor($user->getInstructor());

            return $this->render('course/courses_instructor.html.twig', [
                'courseInstances' => $courseInstances
            ]);
        }
    }

    /**
     * Includes course ID in the URL for readability.
     *
     * @Route("/{courseId}/{instanceId}", name="view_course")
     */
    public function viewCourse($courseId, $instanceId)
    {
        // Check if exists...
        $courseInstance = $this->getDoctrine()
            ->getRepository(CourseInstance::class)
            ->find($instanceId);

        if (!ControllerUtils::coursePathExists($courseInstance, $courseId)) {
            throw $this->createNotFoundException('This course does not exist');
        }

        // Only instructors can access
        $this->denyAccessUnlessGranted(Roles::INSTRUCTOR);

        return $this->render('course/course_summary.html.twig', ['courseInstance' => $courseInstance]);
    }

    /**
     * Includes course ID in the URL for readability.
     *
     * @Route("/{courseId}/{instanceId}/{studentId}", name="view_course_student_summary")
     */
    public function viewCourseStudentSummary($courseId, $instanceId, $studentId)
    {
        $entityManager = $this->getDoctrine();

        $courseInstanceRepo = $entityManager
            ->getRepository(CourseInstance::class);

        $courseInstance = $courseInstanceRepo->find($instanceId);

        $studentRepo = $entityManager
            ->getRepository(Student::class);

        $student = $studentRepo->find($studentId);

        // ...and courseid matches up with the instance id
        if (!($this->coursePathExists($courseInstance, $courseId) && $student)) {
            throw $this->createNotFoundException('The course with this student does not exist');
        }

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if instructor on that course or the same student
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        $labRepo = $entityManager
            ->getRepository(Lab::class);

        $labs = $labRepo
            ->findByCourseInstance($courseInstance);

        $completedLabs = $labRepo
            ->findCompletedSurveysByCourseInstanceAndStudent($courseInstance, $student);

        $pendingLabs = $labRepo
            ->findPendingSurveysByCourseInstanceAndStudent($courseInstance, $student);

        return $this->render('course/student_summary.html.twig', [
            'user' => $this->getUser(),
            'courseInstance' => $courseInstance,
            'pendingLabs' => $pendingLabs,
            'completedLabs' => $completedLabs,
            'student' => $student
        ]);
    }
}
