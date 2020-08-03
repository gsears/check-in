<?php

/*
CourseController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Containers\EnrolmentRisk;
use App\Entity\Lab;
use App\Security\Roles;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Repository\LabRepository;
use App\Form\Type\LabResponseType;
use App\Form\Type\LabDangerZoneType;
use App\Security\Voter\StudentVoter;
use App\Entity\LabXYQuestionResponse;
use App\Repository\StudentRepository;
use App\Entity\SurveyQuestionInterface;
use App\Security\Voter\CourseInstanceVoter;
use App\Form\Type\LabXYQuestionResponseType;
use App\Repository\CourseInstanceRepository;
use App\Form\Type\SurveyQuestionResponseType;
use App\Provider\DateTimeProvider;
use App\Repository\EnrolmentRepository;
use App\Repository\LabResponseRepository;
use App\Task\FlagStudentsTask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    /**
     * Symfony injects in the CourseInstanceRepository.
     *
     * @Route("", name="courses")
     */
    public function index(CourseInstanceRepository $courseInstanceRepo, LabRepository $labRepo)
    {
        $user = $this->getUser();

        $this->denyAccessUnlessGranted(Roles::LOGGED_IN);

        if ($user->isStudent()) {
            $student = $user->getStudent();
            $courseInstances = $courseInstanceRepo->findByStudent($user->getStudent());
            $pendingLabs = $labRepo->findLatestPendingByStudent($student, 5);
            return $this->render('course/courses_student.html.twig', [
                'courseInstances' => $courseInstances,
                'studentId' => $student->getGuid(),
                'recentLabs' => $pendingLabs,
            ]);
        }

        if ($user->isInstructor()) {
            $instructor = $user->getInstructor();
            $courseInstances = $courseInstanceRepo->findByInstructor($instructor);
            $recentLabs = $labRepo->findLatestByInstructor($instructor, 5);
            return $this->render('course/courses_instructor.html.twig', [
                'courseInstances' => $courseInstances,
                'recentLabs' => $recentLabs
            ]);
        }

        $this->createAccessDeniedException("Invalid user role");
    }

    /**
     * Symfony injects in the LabRepository.
     *
     * Includes course ID in the URL for readability.
     *
     * @Route("/{courseId}/{instanceIndex}", name="view_course_summary")
     */
    public function viewCourse(
        $courseId,
        $instanceIndex,
        CourseInstanceRepository $courseInstanceRepo,
        LabRepository $labRepo,
        EnrolmentRepository $enrolmentRepo,
        FlagStudentsTask $flagStudentsTask
    ) {

        // DATA
        $courseInstance = $courseInstanceRepo->findByIndexAndCourse($instanceIndex, $courseId);
        if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');

        // SECURITY

        // Only member instructors can access
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // HANDLER

        $labs = $labRepo->findBy([
            'courseInstance' => $courseInstance
        ]);

        // Find students at risk
        $studentsAtRisk = $enrolmentRepo->findEnrolmentRisksByCourseInstance($courseInstance, true);

        // $flagStudentsTask->run();

        return $this->render('course/course_summary.html.twig', [
            'courseInstance' => $courseInstance,
            'studentsAtRisk' => $studentsAtRisk,
            'labs' => $labs,
            'currentDate' => (new DateTimeProvider)->getCurrentDateTime(),
        ]);
    }

    /**
     * Includes course ID in the URL for readability.
     *
     * @Route("/{courseId}/{instanceIndex}/{studentId}", name="view_course_student_summary")
     */
    public function viewCourseStudentSummary(
        $courseId,
        $instanceIndex,
        $studentId,
        EntityManagerInterface $entityManager,
        CourseInstanceRepository $courseInstanceRepo,
        StudentRepository $studentRepo,
        LabRepository $labRepo,
        LabResponseRepository $labResponseRepo
    ) {

        // DATA

        $courseInstance = $courseInstanceRepo->findByIndexAndCourse($instanceIndex, $courseId);
        if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');

        $student = $studentRepo->find($studentId);
        if (!$student) throw $this->createNotFoundException('This student does not exist');

        // SECURITY

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if instructor on that course or the same student
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        // HANDLER

        $completedLabs = $labRepo->findCompletedSurveysByCourseInstanceAndStudent($courseInstance, $student);
        $pendingLabs = $labRepo->findPendingSurveysByCourseInstanceAndStudent($courseInstance, $student);

        $completedLabResponses =  $labResponseRepo->findCompletedByCourseInstanceAndStudent($courseInstance, $student);

        $completedLabsWithRisk = array_map(function (LabResponse $labResponse) use ($labResponseRepo) {
            return [
                'lab' => $labResponse->getLab(),
                'weightedRisks' => $labResponseRepo->getRiskForResponse($labResponse)->getWeightedRisks(),
            ];
        }, $completedLabResponses);

        return $this->render('course/student_summary.html.twig', [
            'user' => $this->getUser(),
            'courseInstance' => $courseInstance,
            'pendingLabs' => $pendingLabs,
            'completedLabsWithRisk' => $completedLabsWithRisk,
            'student' => $student
        ]);
    }

    /**
     * Includes course ID in the URL for readability.
     *
     * @Route("/{courseId}/{instanceIndex}/lab/{labSlug}", name="view_lab_summary")
     */
    public function viewLabSummary(
        Request $request,
        $courseId,
        $instanceIndex,
        $labSlug,
        CourseInstanceRepository $courseInstanceRepo,
        LabRepository $labRepo
    ) {

        // DATA
        $courseInstance = $courseInstanceRepo->findByIndexAndCourse($instanceIndex, $courseId);
        if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');

        $lab = $labRepo->findOneBy([
            "slug" => $labSlug
        ]);

        if (!$lab) throw $this->createNotFoundException('This lab does not exist');

        // SECURITY

        // Only people who can edit this course instance are allowed to view (i.e. instructors on the course)
        // $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // HANDLER

        // Danger Zone Form

        $form = $this->createForm(LabDangerZoneType::class, $lab);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Danger zones have been updated

            // $form->getData() holds the submitted survey question response
            $updatedLab = $form->getData();

            // Update the database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($updatedLab);
            $entityManager->flush();
        }

        // Display students at risk for lab
        $labResponseRisks = $labRepo->findStudentsAtRiskByLab($lab);

        return $this->render('lab/lab_summary.html.twig', [
            'courseName' => $courseInstance->getName(),
            'labName' => $lab->getName(),
            'form' => $form->createView(),
            'labResponseRisks' => $labResponseRisks,
        ]);
    }

    /**
     * @Route("/{courseId}/{instanceIndex}/lab/{labSlug}/{studentId}", name="lab_survey_view")
     */
    public function viewSurveyResponse(
        Request $request,
        $courseId,
        $instanceIndex,
        $labSlug,
        $studentId,
        CourseInstanceRepository $courseInstanceRepo,
        LabRepository $labRepo,
        StudentRepository $studentRepo,
        LabResponseRepository $labResponseRepo
    ) {
        // DATA

        $courseInstance = $courseInstanceRepo->findByIndexAndCourse($instanceIndex, $courseId);
        if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');

        $lab = $labRepo->findOneBy([
            "slug" => $labSlug
        ]);
        if (!$lab) throw $this->createNotFoundException('This lab does not exist');

        $student = $studentRepo->find($studentId);
        if (!$student) throw $this->createNotFoundException("This student does not exist");

        // Response always exists, so no error checking
        $labResponse = $labResponseRepo->findOneByLabAndStudent($lab, $student);

        // SECURITY
        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if the user is the owning student or instructor, otherwise deny
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        // HANDLER
        $form = $this->createForm(LabResponseType::class, $labResponse, [
            'read_only' => true
        ]);

        return $this->render('lab/response.html.twig', [
            'courseName' => $courseInstance->getName(),
            'labName' => $lab->getName(),
            'form' => $form->createView()
        ]);
    }

    /**
     * !page always generates the page number in the URL. Checks if page is a number.
     *
     * @Route("/{courseId}/{instanceIndex}/lab/{labSlug}/{studentId}/survey/{!page}",
     *      name="lab_survey_response",
     *      requirements={"page"="\d+"})
     *
     */
    public function labSurvey(
        Request $request,
        $courseId,
        $instanceIndex,
        $labSlug,
        $studentId,
        int $page = 1,
        CourseInstanceRepository $courseInstanceRepo,
        LabRepository $labRepo,
        StudentRepository $studentRepo,
        LabResponseRepository $labResponseRepo
    ) {

        // DATA

        $courseInstance = $courseInstanceRepo->findByIndexAndCourse($instanceIndex, $courseId);
        if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');

        $lab = $labRepo->findOneBy([
            "slug" => $labSlug
        ]);

        if (!$lab) throw $this->createNotFoundException('This lab does not exist');

        $student = $studentRepo->find($studentId);
        if (!$student) throw $this->createNotFoundException("This student does not exist");

        // Check if question exists. Out of bounds exception means it doesn't.
        try {
            $question = $lab->getQuestions()->toArray()[$page - 1];
        } catch (\Throwable $th) {
            throw $this->createNotFoundException('This lab survey question does not exist');
        }

        // Response always exists, so no error checking
        $labResponse = $labResponseRepo->findOneByLabAndStudent($lab, $student);

        // SECURITY

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if the user is the owning student, otherwise deny
        $this->denyAccessUnlessGranted(StudentVoter::EDIT, $student);
        // Avoid jumping halfway into the form. Referrer must be a previous question.
        $this->checkValidLabSurveyReferrer($request, $page);

        // HANDLER

        $form = $this->generateLabSurveyForm($question, $labResponse);
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
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($questionResponse);
                    $entityManager->flush();
                }

                // Redirect accordingly...
                if ($page < $lab->getQuestionCount()) {
                    // Get next page in the survey
                    return $this->redirectToRoute('lab_survey_response', [
                        'courseId' => $courseId,
                        'instanceIndex' => $instanceIndex,
                        'labSlug' => $labSlug,
                        'studentId' => $studentId,
                        'page' => $page + 1
                    ]);
                } else {
                    // The form is completed. Even if everything was skipped!
                    $labResponse->setSubmitted(true);

                    // Update it in the database
                    $this->getDoctrine()->getManager()->flush();
                    // Back to summary
                    return $this->redirectToRoute('view_course_student_summary', [
                        'courseId' => $courseId,
                        'instanceIndex' => $instanceIndex,
                        'studentId' => $studentId
                    ]);
                }
            }
        }

        // Render the form. If there are submission errors, they will be displayed too.
        return $this->render('lab/survey_page.html.twig', [
            'courseName' => $courseInstance->getName(),
            'labName' => $lab->getName(),
            'form' => $form->createView(),
        ]);
    }

    private function checkValidLabSurveyReferrer(Request $request, $page)
    {
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
    }

    private function generateLabSurveyForm(SurveyQuestionInterface $question, LabResponse $labResponse): FormInterface
    {
        if ($question instanceof LabXYQuestion) {

            $xyQuestionResponses = $labResponse->getXYQuestionResponses();
            $this->getDoctrine()->getManager()->initializeObject($xyQuestionResponses);

            // Get the response that matches the question
            $questionResponse = $labResponse->getXYQuestionResponses()->filter(
                function (LabXYQuestionResponse $xyQuestionResponse) use ($question) {
                    return $xyQuestionResponse->getLabXYQuestion() === $question;
                }
            )->first();

            // If it doesn't exist, create a new empty one
            if (!$questionResponse) {
                $questionResponse = new LabXYQuestionResponse();
                $questionResponse->setLabXYQuestion($question);
                $questionResponse->setLabResponse($labResponse);
            }

            $form = $this->createForm(LabXYQuestionResponseType::class,  $questionResponse);
        }

        return $form;
    }
}
