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
use App\Form\Type\LabSurveyXYQuestionResponseType;
use App\Form\Type\SurveyQuestionResponseType;
use App\Form\Type\XYCoordinatesType;
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
     * @Route("/courses/{courseId}/{instanceId}", name="view_course")
     */
    public function viewCourse($courseId, $instanceId)
    {
        // Check if exists...
        $courseInstance = $this->getDoctrine()
            ->getRepository(CourseInstance::class)
            ->find($instanceId);

        if (!$this->coursePathExists($courseInstance, $courseId)) {
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
        if (!($this->coursePathExists($courseInstance, $courseId) && $student)) {
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
            ->findCompletedSurveysByCourseInstanceAndStudent($courseInstance, $student);

        $pendingLabs = $labSurveyRepo
            ->findPendingSurveysByCourseInstanceAndStudent($courseInstance, $student);

        return $this->render('course/student_summary.html.twig', [
            'user' => $this->getUser(),
            'courseInstance' => $courseInstance,
            'pendingLabs' => $pendingLabs,
            'completedLabs' => $completedLabs,
            'student' => $student
        ]);
    }

    /**
     * !page always generates the page number in the URL
     * Optional
     * @Route("/courses/{courseId}/{instanceId}/lab/{labId}/{studentId}/{!page}", name="lab_survey_response", requirements={"page"="\d+"})
     */
    public function completeLabSurvey(Request $request, $courseId, $instanceId, $labId, $studentId, int $page = 1)
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

        if (!($this->coursePathExists($courseInstance, $courseId) && $lab && $student)) {
            throw $this->createNotFoundException('This lab survey does not exist');
        }

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if the user is the owning student
        $this->denyAccessUnlessGranted(StudentVoter::EDIT, $student);

        try {
            $question = $lab->getQuestions()->toArray()[$page - 1];
        } catch (\Throwable $th) {
            throw $this->createNotFoundException('This lab survey question does not exist');
        }

        // Generate form:

        $labSurveyResponseRepo = $entityManager
            ->getRepository(LabSurveyResponse::class);

        // There is always a response object for each student
        $response = $labSurveyResponseRepo->findOneByLabSurveyAndStudent($lab, $student);

        // Is there another question?
        $hasNextQuestion = $page < $lab->getQuestionCount();

        // Perform action depending on question type
        if ($question instanceof LabSurveyXYQuestion) {

            // Get the response that matches the question
            $questionResponse = $response->getXYQuestionResponses()->filter(
                function (LabSurveyXYQuestionResponse $xyQuestionResponse) use ($question) {
                    return $xyQuestionResponse->getLabSurveyXYQuestion() === $question;
                }
            )->first();

            // If it doesn't exist, create a new empty one
            if (!$questionResponse) {
                $questionResponse = new LabSurveyXYQuestionResponse();
                $question->addResponse($questionResponse);
                $response->addXyQuestionResponse($questionResponse);
            }

            $form = $this->createForm(LabSurveyXYQuestionResponseType::class,  $questionResponse, [
                'has_next' => $hasNextQuestion
            ]);
        }

        // Handle form:

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // Check if skipped
            $skipped = $form->get(SurveyQuestionResponseType::SKIP_BUTTON_NAME)->isClicked();

            if (!$skipped && $form->isValid()) {
                // $form->getData() holds the submitted survey question response
                $questionResponse = $form->getData();

                // Save the response to the database.
                $entityManager->getManager()->persist($questionResponse);
                $entityManager->getManager()->flush();
            }

            // Redirect accordingly...
            if ($hasNextQuestion) {
                // Get next page in the survey
                return $this->redirectToRoute('lab_survey_response', [
                    'courseId' => $courseId,
                    'instanceId' => $instanceId,
                    'labId' => $labId,
                    'studentId' => $studentId,
                    'page' => $page + 1
                ]);
            } else {
                // We've completed the form. Update the lab response to completed
                $response->setSubmitted(true);
                // Update the database changes
                $entityManager->getManager()->flush();

                // Back to summary
                return $this->redirectToRoute('view_course_student_summary', [
                    'courseId' => $courseId,
                    'instanceId' => $instanceId,
                    'studentId' => $studentId
                ]);
            }
        }

        return $this->render('labsurvey/page.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function coursePathExists($courseInstance, $courseId): bool
    {
        if ($courseInstance) {
            $courseCode = $courseInstance->getCourse()->getCode();
            return $courseCode === $courseId;
        }

        return false;
    }
}
