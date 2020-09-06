<?php

/*
CourseController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Entity\Lab;
use App\Entity\Student;
use App\Security\Roles;
use App\Entity\Enrolment;
use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Entity\CourseInstance;
use App\Repository\LabRepository;
use App\Provider\DateTimeProvider;
use App\Form\Type\RiskSettingsType;
use App\Entity\LabSentimentQuestion;
use App\Form\Type\LabDangerZoneType;
use App\Security\Voter\StudentVoter;
use App\Entity\LabXYQuestionResponse;
use App\Entity\SurveyQuestionInterface;
use App\Repository\EnrolmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LabResponseRepository;
use Symfony\Component\Form\FormInterface;
use App\Security\Voter\CourseInstanceVoter;
use App\Entity\LabSentimentQuestionResponse;
use App\Form\Type\LabXYQuestionResponseType;
use App\Form\Type\RiskFlagType;
use App\Repository\CourseInstanceRepository;
use App\Form\Type\SurveyQuestionResponseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Type\LabSentimentQuestionResponseType;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    // Route Path Names
    const COURSES_PAGE = 'course_instances';
    const COURSE_SUMMARY_PAGE = 'course_instance_summary';
    const STUDENT_SUMMARY_PAGE = 'view_course_student_summary';
    const LAB_SUMMARY_PAGE = 'view_lab_summary';
    const LAB_SURVEY_PAGE = 'lab_survey_response';

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
     * @Route("", name=CourseController::COURSES_PAGE)
     */
    public function index(CourseInstanceRepository $courseInstanceRepo, LabRepository $labRepo, EnrolmentRepository $enrolmentRepo)
    {
        $user = $this->getUser();

        $this->denyAccessUnlessGranted(Roles::LOGGED_IN);

        $breadcrumbs = [
            ['name' => 'Courses']
        ];

        if ($user->isStudent()) {
            $student = $user->getStudent();
            $enrolments = $enrolmentRepo->findBy([
                'student' => $student
            ]);

            $pendingLabs = $labRepo->findLatestPendingByStudent($student, 5);
            return $this->render('course/courses_student.html.twig', [
                'enrolments' => $enrolments,
                'studentId' => $student->getGuid(),
                'recentLabs' => $pendingLabs,
                'breadcrumbArray' => $breadcrumbs
            ]);
        }

        if ($user->isInstructor()) {
            $instructor = $user->getInstructor();
            $courseInstances = $courseInstanceRepo->findByInstructor($instructor);
            $recentLabs = $labRepo->findLatestByInstructor($instructor, 5);
            return $this->render('course/courses_instructor.html.twig', [
                'courseInstances' => $courseInstances,
                'recentLabs' => $recentLabs,
                'breadcrumbArray' => $breadcrumbs
            ]);
        }

        $this->createAccessDeniedException("Invalid user role");
    }

    /**
     * @Route("/{courseId}/{instanceIndex}", name=CourseController::COURSE_SUMMARY_PAGE)
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
        $labs = $labRepo->findBy(['courseInstance' => $courseInstance], ['startDateTime' => 'DESC']);

        /**
         * Get students at risk in course
         * @var EnrolmentRepository
         */
        $enrolmentRepo = $this->entityManager->getRepository(Enrolment::class);
        $enrolmentRisks = $enrolmentRepo->findEnrolmentRisksByCourseInstance($courseInstance, false);

        return $this->render('course/course_summary.html.twig', [
            'courseInstance' => $courseInstance,
            'enrolmentRisks' => $enrolmentRisks,
            'labs' => $labs,
            'currentDate' => (new DateTimeProvider)->getCurrentDateTime(),
            'riskSettingsForm' => $riskSettingsForm->createView(),
            'breadcrumbArray' => [
                ['name' => 'Courses', 'href' => $this->generateUrl(self::COURSES_PAGE)],
                ['name' => $courseId . ' - ' . $instanceIndex]
            ]
        ]);
    }

    /**
     * @Route("/{courseId}/{instanceIndex}/{studentId}", name=CourseController::STUDENT_SUMMARY_PAGE)
     */
    public function viewCourseStudentSummary(
        Request $request,
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
        $completedLabResponseRisks = array_map(function (LabResponse $labResponse) use ($labResponseRepo) {
            return $labResponseRepo->getLabResponseRisk($labResponse);
        }, $completedLabResponses);

        /**
         * Get enrolment for risk flag information for this course
         * @var EnrolmentRepository
         */
        $enrolmentRepo = $this->entityManager->getRepository(Enrolment::class);

        $enrolment = $enrolmentRepo->findOneBy([
            'student' => $student,
            'courseInstance' => $courseInstance
        ]);

        $user = $this->getUser();

        $flagForm = $this->createForm(RiskFlagType::class, $enrolment, [
            RiskFlagType::USER_ROLES => $user->getRoles()
        ]);


        $flagForm->handleRequest($request);

        if ($flagForm->isSubmitted() && $flagForm->isValid()) {

            try {
                $manualFlagSubmit = $flagForm->get(RiskFlagType::MANUAL_FLAG_BUTTON);
            } catch (\OutOfBoundsException $e) {
                $manualFlagSubmit = null;
            }

            try {
                $removeFlagSubmit = $flagForm->get(RiskFlagType::REMOVE_FLAG_BUTTON);
            } catch (\OutOfBoundsException $e) {
                $removeFlagSubmit = null;
            }

            if ($manualFlagSubmit && $manualFlagSubmit->isClicked()) {
                /**
                 * @var Enrolment
                 */
                $enrolment = $flagForm->getData();
                if ($user->isStudent()) {
                    $riskFlag = Enrolment::FLAG_BY_STUDENT;
                } else if ($user->isInstructor()) {
                    $riskFlag = Enrolment::FLAG_BY_INSTRUCTOR;
                } else {
                    throw new LogicException("Invalid user type setting flag");
                }

                $enrolment->setRiskFlag($riskFlag, $flagForm->get(RiskFlagType::DESCRIPTION_INPUT)->getData());
            } else if ($removeFlagSubmit && $removeFlagSubmit->isClicked()) {
                /**
                 * @var Enrolment
                 */
                $enrolment = $flagForm->getData();
                $enrolment->removeRiskFlag();
            } else {
                // This should be unreachable
                throw new LogicException("Invalid submission");
            }

            // Update db
            $this->entityManager->flush();

            // Redirect to update form
            return $this->redirectToRoute(self::STUDENT_SUMMARY_PAGE, [
                'courseId' => $courseId,
                'instanceIndex' => $instanceIndex,
                'studentId' => $studentId
            ]);
        }

        // Create breadcrumbs

        $coursesPageHref = $this->generateUrl(self::COURSES_PAGE);

        if ($user->isStudent()) {
            $breadcrumbs = [
                ['name' => 'Courses', 'href' => $coursesPageHref],
                ['name' => $courseId . ' - ' . $instanceIndex],
                ['name' => $studentId . ' - ' . $student->getUser()->getFullName()]
            ];
        } else {
            $breadcrumbs = [
                ['name' => 'Courses', 'href' => $coursesPageHref],
                [
                    'name' => $courseId . ' - ' . $instanceIndex,
                    'href' => $this->generateUrl(self::COURSE_SUMMARY_PAGE, [
                        'courseId' => $courseId,
                        'instanceIndex' => $instanceIndex
                    ])
                ],
                ['name' => $studentId . ' - ' . $student->getUser()->getFullName()]
            ];
        }

        return $this->render('course/student_summary.html.twig', [
            'user' => $this->getUser(),
            'courseInstance' => $courseInstance,
            'pendingLabs' => $pendingLabs,
            'completedLabResponseRisks' => $completedLabResponseRisks,
            'student' => $student,
            'enrolment' => $enrolment,
            'breadcrumbArray' => $breadcrumbs,
            'flagForm' => $flagForm->createView()
        ]);
    }

    /**
     * @Route("/{courseId}/{instanceIndex}/lab/{labSlug}", name=CourseController::LAB_SUMMARY_PAGE)
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
        $labResponseRisks = $labRepo->getLabResponseRisks($lab);

        return $this->render('lab/lab_summary.html.twig', [
            'courseInstance' => $courseInstance,
            'lab' => $lab,
            'form' => $form->createView(),
            'labResponseRisks' => $labResponseRisks,
            'breadcrumbArray' =>  [
                ['name' => 'Courses', 'href' => $this->generateUrl(self::COURSES_PAGE)],
                ['name' => $courseId . ' - ' . $instanceIndex, 'href' => $this->generateUrl(self::COURSE_SUMMARY_PAGE, [
                    'courseId' => $courseId,
                    'instanceIndex' => $instanceIndex
                ])],
                ['name' => $lab->getName()]
            ]
        ]);
    }

    /**
     * !page always generates the page number in the URL. Checks if page is a number.
     *
     * @Route("/{courseId}/{instanceIndex}/lab/{labSlug}/{studentId}/survey/{!page}",
     *      name=CourseController::LAB_SURVEY_PAGE,
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
        } catch (\OutOfBoundsException $e) {
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
        $questionCount = $lab->getQuestionCount();
        $isLastQuestion = $page === $questionCount;

        $form = $this->generateLabSurveyForm($question, $labResponse, [
            'submitText' => $isLastQuestion ? 'Submit' : 'Next Question',
            'skipText' => $isLastQuestion ? 'Skip and Submit' : 'Skip Question',
        ]);

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
                if ($isLastQuestion) {
                    // The form is completed. Even if everything was skipped!
                    $labResponse->setSubmitted(true);
                    $this->entityManager->flush();

                    // Back to summary
                    return $this->redirectToRoute(self::STUDENT_SUMMARY_PAGE, [
                        'courseId' => $courseId,
                        'instanceIndex' => $instanceIndex,
                        'studentId' => $studentId
                    ]);
                } else {
                    // Get next page in the survey
                    return $this->redirectToRoute(self::LAB_SURVEY_PAGE, [
                        'courseId' => $courseId,
                        'instanceIndex' => $instanceIndex,
                        'labSlug' => $labSlug,
                        'studentId' => $studentId,
                        'page' => $page + 1
                    ]);
                }
            }
        }

        // Render the form. If there are submission errors, they will be displayed too.
        return $this->render('lab/survey_page.html.twig', [
            'courseName' => $courseInstance->getName(),
            'labName' => $lab->getName(),
            'form' => $form->createView(),
            'questionNumber' => $page,
            'questionCount' => $questionCount,
            'breadcrumbArray' =>  [
                ['name' => 'Courses', 'href' => $this->generateUrl(self::COURSES_PAGE)],
                ['name' => $courseId . ' - ' . $instanceIndex],
                [
                    'name' => $studentId . ' - ' . $student->getUser()->getFullName(),
                    'href' => $this->generateUrl(self::STUDENT_SUMMARY_PAGE, [
                        'courseId' => $courseId,
                        'instanceIndex' => $instanceIndex,
                        'studentId' => $studentId
                    ])
                ],
                ['name' => $lab->getName() . ' - Question ' . $page]
            ]
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

    private function generateLabSurveyForm(SurveyQuestionInterface $question, LabResponse $labResponse, array $formOptions = []): FormInterface
    {
        if ($question instanceof LabXYQuestion) {

            // Get the response that matches the question
            $labXYQuestionResponseRepo = $this->entityManager->getRepository(LabXYQuestionResponse::class);

            $questionResponse = $labXYQuestionResponseRepo->findOneBy([
                'labXYQuestion' => $question,
                'labResponse' => $labResponse
            ]);

            // If it doesn't exist, create a new empty one
            if (!$questionResponse) {
                $questionResponse = new LabXYQuestionResponse();
                $questionResponse->setLabXYQuestion($question);
                $questionResponse->setLabResponse($labResponse);
            }

            $form = $this->createForm(LabXYQuestionResponseType::class,  $questionResponse, $formOptions);
        }

        if ($question instanceof LabSentimentQuestion) {

            // Get the response that matches the question
            $labXYQuestionResponseRepo = $this->entityManager->getRepository(LabSentimentQuestionResponse::class);

            $questionResponse = $labXYQuestionResponseRepo->findOneBy([
                'labSentimentQuestion' => $question,
                'labResponse' => $labResponse
            ]);

            // If it doesn't exist, create a new empty one
            if (!$questionResponse) {
                $questionResponse = new LabSentimentQuestionResponse();
                $questionResponse->setLabSentimentQuestion($question);
                $questionResponse->setLabResponse($labResponse);
            }

            $form = $this->createForm(LabSentimentQuestionResponseType::class,  $questionResponse, $formOptions);
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

            $courseInstance = $courseInstanceRepo->findByIndexAndCourseCode(
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
