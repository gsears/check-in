<?php

namespace App\Controller;

use App\Entity\CourseInstance;
use App\Entity\LabSurvey;
use App\Entity\LabSurveyResponse;
use App\Entity\LabSurveyXYQuestion;
use App\Entity\LabSurveyXYQuestionResponse;
use App\Entity\Student;
use App\Entity\User;
use App\Entity\XYQuestion;
use App\Form\Type\LabSurveyResponseType;
use App\Form\Type\XYQuestionType;
use App\Security\Roles;
use App\Security\Voter\CourseInstanceVoter;
use App\Security\Voter\StudentVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    /**
     * @Route("/courses", name="courses")
     */
    public function index()
    {
        $user = $this->getUser();

        if($user->isStudent()) {
            $courseInstances = $this->getDoctrine()
                ->getRepository(CourseInstance::class)
                ->findByStudent($user->getStudent());

            return $this->render('course/courses_student.html.twig', [
                'courseInstances' => $courseInstances,
                'studentId' => $user->getStudent()->getGuid()
            ]);
        }

        if($user->isInstructor()) {
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
     * @Route("/courses/{courseId}/{instanceId}", name="view_course")
     */
    public function viewCourse($courseId, $instanceId)
    {
        // Check if exists...
        $courseInstance = $this->getDoctrine()
            ->getRepository(CourseInstance::class)
            ->find($instanceId);

        if(!$this->coursePathExists($courseInstance, $courseId)) {
            throw $this->createNotFoundException('This course does not exist');
        }

        // Only instructors can access
        $this->denyAccessUnlessGranted(Roles::INSTRUCTOR);

        return $this->render('course/course_summary.html.twig', ['courseInstance' => $courseInstance]);
    }

     /**
     * Includes course ID in the URL for readability.
     *
     * @Route("/courses/{courseId}/{instanceId}/{studentId}", name="view_course_student_summary")
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
        if(!($this->coursePathExists($courseInstance, $courseId) && $student)) {
            throw $this->createNotFoundException('The course with this student does not exist');
        }

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if instructor on that course or the same student
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        $labSurveyRepo = $entityManager
            ->getRepository(LabSurvey::class);

        $labs = $labSurveyRepo
            ->findByCourseInstance($courseInstance);

        $completedLabs = $labSurveyRepo
            -> findCompletedSurveysByCourseInstanceAndStudent($courseInstance, $student);

        $pendingLabs = array_filter($labs, function($lab) use ($completedLabs) {
            return !in_array($lab, $completedLabs);
        });

        return $this->render('course/student_summary.html.twig', [
            'user' => $this->getUser(),
            'courseInstance' => $courseInstance,
            'pendingLabs' => $pendingLabs,
            'completedLabs' => $completedLabs,
            'student' => $student
        ]);
    }

    /**
     * Optional
     * @Route("/courses/{courseId}/{instanceId}/lab/{labId}/{studentId}", name="lab_survey_response")
     */
    public function completeLabSurvey(Request $request, $courseId, $instanceId, $labId, $studentId)
    {
        // Security and sanity checks:

        $entityManager = $this->getDoctrine();

        $courseInstanceRepo = $entityManager
            ->getRepository(CourseInstance::class);

        $courseInstance = $courseInstanceRepo->find($instanceId);

        $studentRepo = $entityManager
            ->getRepository(Student::class);

        $student = $studentRepo->find($studentId);

        $labRepo = $entityManager
            ->getRepository(LabSurvey::class);

        $lab = $labRepo->find($labId);

        if(!($this->coursePathExists($courseInstance, $courseId) && $lab && $student)) {
            throw $this->createNotFoundException('This lab survey does not exist');
        }

         // Check permission to view course instance
         $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
         // Check if the user is the owning student
         $this->denyAccessUnlessGranted(StudentVoter::EDIT, $student);


        // Generate form:

        // Create new response object
        $labSurveyResponse = new LabSurveyResponse();
        $lab->addResponse($labSurveyResponse);
        $student->addLabSurveyResponse($labSurveyResponse);

        // Add dummy responses for each XYQuestion in the lab survey
        foreach ($lab->getXyQuestions()->toArray() as $xyQuestion) {

            $xyQuestionResponse = new LabSurveyXYQuestionResponse();
            $xyQuestion->addResponse($xyQuestionResponse);
            $labSurveyResponse->addXyQuestionResponse($xyQuestionResponse);
            // other values will be obtained via the form object
        }

        $form = $this->createForm(LabSurveyResponseType::class,  $labSurveyResponse);

        // Return to course summary page after submission.
        $redirect = $this->generateUrl('view_course_student_summary', [
            'courseId' => $courseId,
            'instanceId' => $instanceId,
            'studentId' => $studentId
        ]);

         return $this->render('labsurvey/page.html.twig', [
            'form' => $form->createView(),
            'redirect' => $redirect
        ]);
    }

    private function coursePathExists($courseInstance, $courseId) : bool
    {
            if($courseInstance) {
                $courseCode = $courseInstance->getCourse()->getCode();
                return $courseCode === $courseId;
            }

            return false;
    }
}
