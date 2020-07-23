<?php

namespace App\Controller;

use App\Entity\CourseInstance;
use App\Entity\Lab;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Entity\LabXYQuestionResponse;
use App\Entity\Student;
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
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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

    /**
     * !page always generates the page number in the URL
     * Optional
     * @Route("/courses/{courseId}/{instanceId}/lab/{labId}/{studentId}/{!page}", name="lab_survey_response", requirements={"page"="\d+"})
     */
    public function completeLab(Request $request, $courseId, $instanceId, $labId, $studentId, int $page = 1)
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
            ->getRepository(Lab::class);

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

        // Check that referrer was previous question, otherwise redirect to first question
        // to avoid skipping.

        if ($page > 1) {
            $referer = $request->headers->get('referer');

            if (!$referer) {
                throw new AccessDeniedHttpException('Cannot skip form entry.');
            }
            $current = $request->getUri();
            $regex = "/(.+)\/(\d+)$/";

            preg_match($regex, $referer, $refererMatch);
            preg_match($regex, $current, $currentMatch);

            $refererBase = $refererMatch[1];
            $currentBase = $currentMatch[1];
            $refererPage = intval($refererMatch[2]);

            if ($refererBase !== $currentBase || !in_array($refererPage, [$page, $page - 1])) {
                throw new AccessDeniedHttpException('Cannot skip form entry.');
            }
        }


        // Generate form:

        $labResponseRepo = $entityManager
            ->getRepository(LabResponse::class);

        // There is always a response object for each student
        $response = $labResponseRepo->findOneByLabAndStudent($lab, $student);

        // Perform action depending on question type
        if ($question instanceof LabXYQuestion) {

            // Get the response that matches the question
            $questionResponse = $response->getXYQuestionResponses()->filter(
                function (LabXYQuestionResponse $xyQuestionResponse) use ($question) {
                    return $xyQuestionResponse->getLabXYQuestion() === $question;
                }
            )->first();

            // If it doesn't exist, create a new empty one
            if (!$questionResponse) {
                $questionResponse = new LabXYQuestionResponse();
                $questionResponse->setLabXYQuestion($question);
                $questionResponse->setLabResponse($response);
            }

            $form = $this->createForm(LabXYQuestionResponseType::class,  $questionResponse);
        }

        // Handle form:

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $skipped = $form->get(SurveyQuestionResponseType::SKIP_BUTTON_NAME)->isClicked();
            $isValid = $form->isValid();

            // If the form is skipped or is valid, redirect to the next page
            if ($skipped || $isValid) {

                // Persist to the database if the form was not skipped
                if (!$skipped  && $isValid) {

                    // $form->getData() holds the submitted survey question response
                    $questionResponse = $form->getData();
                    // Save the response to the database.
                    $entityManager->getManager()->persist($questionResponse);
                    $entityManager->getManager()->flush();
                }

                // Redirect accordingly...
                if ($page < $lab->getQuestionCount()) {
                    // Get next page in the survey
                    return $this->redirectToRoute('lab_survey_response', [
                        'courseId' => $courseId,
                        'instanceId' => $instanceId,
                        'labId' => $labId,
                        'studentId' => $studentId,
                        'page' => $page + 1
                    ]);
                } else {

                    // We've completed the form. Update the lab response to completed...
                    $response->setSubmitted(true);
                    // ...and save to the database.
                    $entityManager->getManager()->flush();

                    // Back to summary
                    return $this->redirectToRoute('view_course_student_summary', [
                        'courseId' => $courseId,
                        'instanceId' => $instanceId,
                        'studentId' => $studentId
                    ]);
                }
            }
        }

        // Render the form. If there are submission errors, they will be displayed too.
        return $this->render('lab/page.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Includes course ID in the URL for readability.
     *
     * @Route("courses/{courseId}/{instanceId}/lab/{labId}/", name="test")
     */
    public function test(Request $request, $courseId, $instanceId, $labId)
    {
        // Security and sanity checks:

        $entityManager = $this->getDoctrine();

        $courseInstanceRepo = $entityManager
            ->getRepository(CourseInstance::class);

        $courseInstance = $courseInstanceRepo->find($instanceId);

        $labRepo = $entityManager
            ->getRepository(Lab::class);

        $lab = $labRepo->find($labId);

        if (!($this->coursePathExists($courseInstance, $courseId) && $lab)) {
            throw $this->createNotFoundException('This lab survey does not exist');
        }

        $form = $this->createForm(LabDangerZoneType::class, $lab);
        $form->handleRequest($request);
        dump($form->isSubmitted());
        if ($form->isSubmitted()) {
            dump("here");
            dump($form->getData());
        }

        return $this->render('lab/page.html.twig', [
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
