<?php

/*
CourseController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Containers\EnrolmentRisk;
use App\Entity\CourseInstance;
use App\Entity\Enrolment;
use App\Entity\Lab;
use App\Security\Roles;
use App\Entity\LabResponse;
use App\Entity\LabSentimentQuestion;
use App\Entity\LabSentimentQuestionResponse;
use App\Entity\LabXYQuestion;
use App\Repository\LabRepository;
use App\Form\Type\LabResponseType;
use App\Form\Type\LabDangerZoneType;
use App\Security\Voter\StudentVoter;
use App\Entity\LabXYQuestionResponse;
use App\Entity\Student;
use App\Repository\StudentRepository;
use App\Entity\SurveyQuestionInterface;
use App\Form\Type\LabSentimentQuestionResponseType;
use App\Security\Voter\CourseInstanceVoter;
use App\Form\Type\LabXYQuestionResponseType;
use App\Form\Type\RiskSettingsType;
use App\Repository\CourseInstanceRepository;
use App\Form\Type\SurveyQuestionResponseType;
use App\Provider\DateTimeProvider;
use App\Repository\EnrolmentRepository;
use App\Repository\LabResponseRepository;
use App\Task\FlagStudentsTask;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use OutOfBoundsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    // Used for error checking routes
    const COURSE_QUERY = 'course';
    const COURSE_INSTANCE_QUERY = 'course_instance';
    const COURSE_INSTANCE_INDEX = 'course_instance_index';
    const STUDENT_QUERY = 'student';
    const LAB_QUERY_BY_SLUG = 'lab_slug';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
     * @Route("/{courseId}/{instanceIndex}", name="view_course_summary")
     */
    public function viewCourse(
        Request $request,
        $courseId,
        $instanceIndex,
        EnrolmentRepository $enrolmentRepo
    ) {

        // DATA
        $data = $this->fetchData([
            self::COURSE_INSTANCE_QUERY => [
                self::COURSE_QUERY => $courseId,
                self::COURSE_INSTANCE_INDEX => $instanceIndex
            ]
        ]);

        $courseInstance = $data[self::COURSE_INSTANCE_QUERY];

        // SECURITY
        // Only member instructors can access
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // HANDLER
        // Create risk settings form
        $riskSettingsForm = $this->createForm(RiskSettingsType::class, $courseInstance);
        $riskSettingsForm->handleRequest($request);

        if ($riskSettingsForm->isSubmitted() && $riskSettingsForm->isValid()) {

            // Risk thresholds have been updated
            $updatedCourseInstance = $riskSettingsForm->getData();
            $this->entityManager->persist($updatedCourseInstance);
            $this->entityManager->flush();

            // Force rerender of page to update tables
            return $this->redirect($request->getUri());
        }

        /**
         * Get all labs in course
         * @var LabRepository
         */
        $labRepo = $this->entityManager->getRepository(Lab::class);
        $labs = $labRepo->findBy(['courseInstance' => $courseInstance]);

        /**
         * Get students at risk in course
         * @var EnrolmentRepository
         */
        $enrolmentRepo = $this->entityManager->getRepository(Enrolment::class);
        $studentsAtRisk = $enrolmentRepo->findEnrolmentRisksByCourseInstance($courseInstance, true);

        return $this->render('course/course_summary.html.twig', [
            'courseInstance' => $courseInstance,
            'studentsAtRisk' => $studentsAtRisk,
            'labs' => $labs,
            'currentDate' => (new DateTimeProvider)->getCurrentDateTime(),
            'riskSettingsForm' => $riskSettingsForm->createView()
        ]);
    }

    /**
     * @Route("/{courseId}/{instanceIndex}/{studentId}", name="view_course_student_summary")
     */
    public function viewCourseStudentSummary(
        $courseId,
        $instanceIndex,
        $studentId
    ) {

        // DATA
        $data = $this->fetchData([
            self::COURSE_INSTANCE_QUERY => [
                self::COURSE_QUERY => $courseId,
                self::COURSE_INSTANCE_INDEX => $instanceIndex
            ],
            self::STUDENT_QUERY => $studentId,
        ]);

        $courseInstance = $data[self::COURSE_INSTANCE_QUERY];
        $student = $data[self::STUDENT_QUERY];

        // SECURITY
        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if instructor on that course or the same student
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        // HANDLER
        /**
         * Get pending labs up to today's date.
         * @var LabRepository
         */
        $labRepo = $this->entityManager->getRepository(Lab::class);
        $pendingLabs = $labRepo->findPendingSurveysByCourseInstanceAndStudent($courseInstance, $student);

        /**
         * Get completed lab responses and the risk ratings for each question.
         * @var LabResponseRepository
         */
        $labResponseRepo = $this->entityManager->getRepository(LabResponse::class);
        $completedLabResponses =  $labResponseRepo->findCompletedByCourseInstanceAndStudent($courseInstance, $student);
        $completedLabsWithRisk = array_map(function (LabResponse $labResponse) use ($labResponseRepo) {
            return [
                'lab' => $labResponse->getLab(),
                'weightedRisks' => $labResponseRepo->getLabResponseRisk($labResponse)->getWeightedRisks(),
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
     * @Route("/{courseId}/{instanceIndex}/lab/{labSlug}", name="view_lab_summary")
     */
    public function viewLabSummary(
        Request $request,
        $courseId,
        $instanceIndex,
        $labSlug
    ) {

        // DATA
        $data = $this->fetchData([
            self::COURSE_INSTANCE_QUERY => [
                self::COURSE_QUERY => $courseId,
                self::COURSE_INSTANCE_INDEX => $instanceIndex
            ],
            self::LAB_QUERY_BY_SLUG => $labSlug,
        ]);

        $courseInstance = $data[self::COURSE_INSTANCE_QUERY];
        $lab = $data[self::LAB_QUERY_BY_SLUG];

        // SECURITY
        // Only people who can edit this course instance are allowed to view (i.e. instructors on the course)
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // HANDLER
        /**
         * Create danger zone form
         */
        $form = $this->createForm(LabDangerZoneType::class, $lab);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Danger zones have been updated
            $updatedLab = $form->getData();
            $this->entityManager->persist($updatedLab);
            $this->entityManager->flush();
        }

        /**
         * Get students at risk in the lab
         * @var LabRepository
         */
        $labRepo = $this->entityManager->getRepository(Lab::class);
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
        $courseId,
        $instanceIndex,
        $labSlug,
        $studentId
    ) {
        // DATA
        $data = $this->fetchData([
            self::COURSE_INSTANCE_QUERY => [
                self::COURSE_QUERY => $courseId,
                self::COURSE_INSTANCE_INDEX => $instanceIndex
            ],
            self::LAB_QUERY_BY_SLUG => $labSlug,
            self::STUDENT_QUERY => $studentId,
        ]);

        $courseInstance = $data[self::COURSE_INSTANCE_QUERY];
        $lab = $data[self::LAB_QUERY_BY_SLUG];
        $student = $data[self::STUDENT_QUERY];

        /**
         * Response always exists (created when adding lab), so no error checking
         * @var LabResponseRepository
         */
        $labResponseRepo = $this->entityManager->getRepository(LabResponse::class);
        $labResponse = $labResponseRepo->findOneByLabAndStudent($lab, $student);

        // SECURITY
        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if the user is the owning student or instructor, otherwise deny
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        // HANDLER
        // TODO: This should NOT be a form. Need to refactor all of these!
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
        int $page = 1
    ) {
        // DATA
        $data = $this->fetchData([
            self::COURSE_INSTANCE_QUERY => [
                self::COURSE_QUERY => $courseId,
                self::COURSE_INSTANCE_INDEX => $instanceIndex
            ],
            self::LAB_QUERY_BY_SLUG => $labSlug,
            self::STUDENT_QUERY => $studentId,
        ]);

        $courseInstance = $data[self::COURSE_INSTANCE_QUERY];
        $lab = $data[self::LAB_QUERY_BY_SLUG];
        $student = $data[self::STUDENT_QUERY];

        // Check if question exists. Out of bounds exception means it doesn't.
        try {
            $question = $lab->getQuestions()->toArray()[$page - 1];
        } catch (OutOfBoundsException $e) {
            throw $this->createNotFoundException('This lab survey question does not exist');
        }

        /**
         * Response always exists (created when adding lab), so no error checking
         * @var LabResponseRepository
         */
        $labResponseRepo = $this->entityManager->getRepository(LabResponse::class);
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
                    try {
                        $this->processFormData($form);
                    } catch (\MonkeyLearn\MonkeyLearnException $e) {
                        // Render the form with a custom monkeylearn exception
                        // TODO: Throw some kind of exception if API call does not happen...
                        // Probably to instructors / admin
                    }
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
                    $this->entityManager->flush();

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

            // Get the response that matches the question
            $labXYQuestionResponseRepo = $this->entityManager->getRepository(LabXYQuestionResponse::class);

            $questionResponse = $labXYQuestionResponseRepo->findOneBy([
                'labXYQuestion' => $question
            ]);

            // If it doesn't exist, create a new empty one
            if (!$questionResponse) {
                $questionResponse = new LabXYQuestionResponse();
                $questionResponse->setLabXYQuestion($question);
                $questionResponse->setLabResponse($labResponse);
            }

            $form = $this->createForm(LabXYQuestionResponseType::class,  $questionResponse);
        }

        if ($question instanceof LabSentimentQuestion) {

            // Get the response that matches the question
            $labXYQuestionResponseRepo = $this->entityManager->getRepository(LabSentimentQuestionResponse::class);

            $questionResponse = $labXYQuestionResponseRepo->findOneBy([
                'labSentimentQuestion' => $question
            ]);

            // If it doesn't exist, create a new empty one
            if (!$questionResponse) {
                $questionResponse = new LabSentimentQuestionResponse();
                $questionResponse->setLabSentimentQuestion($question);
                $questionResponse->setLabResponse($labResponse);
            }

            $form = $this->createForm(LabSentimentQuestionResponseType::class,  $questionResponse);
        }

        return $form;
    }

    private function processFormData(FormInterface $form)
    {
        $questionResponse = $form->getData();

        if ($questionResponse instanceof LabSentimentQuestionResponse) {
            dump("Here");
            // Make an API call
            $monkeyLearnApiKey = $this->getParameter('app.monkeylearn_api_key');
            $monkeyLearnModel = $this->getParameter("app.monkeylearn_model_id");
            dump($monkeyLearnApiKey);
            dump($monkeyLearnModel);
            $ml = new \MonkeyLearn\Client($monkeyLearnApiKey);
            $res = $ml->classifiers->classify($monkeyLearnModel, [$questionResponse->getText()]);
            // Parse the response
            $data = $res->result[0];
            // $data = json_decode($json);

            dump($data);

            if ($data['error']) {
                throw new \MonkeyLearn\MonkeyLearnException("Bad request from monkeylearn.\n", 1);
            }

            // Set the results on the entity
            $classification = $data['classifications'][0];
            $questionResponse->setClassification($classification['tag_name']);
            $questionResponse->setConfidence($classification['confidence']);
        }

        $this->entityManager->persist($questionResponse);
        $this->entityManager->flush();
        dump("here");
        return;
    }

    /**
     * Pass in a dictionary of query ids, slugs, etc.
     *
     * Does the appropriate error checks and returns a dictionary with the requested entities.
     *
     * @param array $params
     * @return array
     */
    private function fetchData(array $params): array
    {
        $resultDictionary = [];

        // Get course instance
        if (array_key_exists(self::COURSE_INSTANCE_QUERY, $params)) {

            $courseInstanceQuery = $params[self::COURSE_INSTANCE_QUERY];

            /**
             * @var CourseInstanceRepository
             */
            $courseInstanceRepo = $this->entityManager->getRepository(CourseInstance::class);

            $courseInstance = $courseInstanceRepo->findByIndexAndCourse(
                $courseInstanceQuery[self::COURSE_INSTANCE_INDEX],
                $courseInstanceQuery[self::COURSE_QUERY]
            );

            if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');

            $resultDictionary[self::COURSE_INSTANCE_QUERY] = $courseInstance;

            // Get lab in course instance
            if (array_key_exists(self::LAB_QUERY_BY_SLUG, $params)) {

                $labSlug = $params[self::LAB_QUERY_BY_SLUG];
                $labRepo = $this->entityManager->getRepository(Lab::class);

                $lab = $labRepo->findOneBy([
                    "slug" => $labSlug,
                    "courseInstance" => $courseInstance,
                ]);

                if (!$lab) throw $this->createNotFoundException('This lab does not exist in this course');

                $resultDictionary[self::LAB_QUERY_BY_SLUG] = $lab;
            }
        }

        if (array_key_exists(self::STUDENT_QUERY, $params)) {

            $studentId = $params[self::STUDENT_QUERY];
            $studentRepo =  $this->entityManager->getRepository(Student::class);
            $student = $studentRepo->find($studentId);

            if (!$student) throw $this->createNotFoundException('This student does not exist');

            $resultDictionary[self::STUDENT_QUERY] = $student;
        }

        return $resultDictionary;
    }
}
