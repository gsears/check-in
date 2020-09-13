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
use App\Form\Type\RiskFlagType;
use App\Repository\LabRepository;
use App\Provider\DateTimeProvider;
use App\Service\BreadcrumbBuilder;
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
use App\Repository\CourseInstanceRepository;
use App\Form\Type\SurveyQuestionResponseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Type\LabSentimentQuestionResponseType;
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
    public function index(
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
        $this->createAccessDeniedException("Invalid user role");
    }

    /**
     * @Route("/{courseId}/{instanceIndex}", name=CourseController::COURSE_SUMMARY_PAGE)
     */
    public function viewCourse(
        Request $request,
        $instanceIndex,
        $courseId,
        LabRepository $labRepo,
        EnrolmentRepository $enrolmentRepo,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {

        $courseInstance = $this->fetchCourseInstance($instanceIndex, $courseId);

        // SECURITY
        // Only member instructors can access
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // HANDLER
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
            ->addRoute('Courses', self::COURSES_PAGE)
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

    /**
     * @Route("/{courseId}/{instanceIndex}/{studentId}", name=CourseController::STUDENT_SUMMARY_PAGE)
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

        // SECURITY
        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if instructor on that course or the same student
        $this->denyAccessUnlessGranted(StudentVoter::VIEW, $student);

        $completedLabResponses =  $labResponseRepo->findCompletedByCourseInstanceAndStudent($courseInstance, $student);
        $completedLabResponseRisks = array_map(function (LabResponse $labResponse) use ($labResponseRepo) {
            return $labResponseRepo->getLabResponseRisk($labResponse);
        }, $completedLabResponses);

        $enrolment = $enrolmentRepo->findOneBy([
            'student' => $student,
            'courseInstance' => $courseInstance
        ]);

        // FLAG FORM
        $user = $this->getUser();

        $flagForm = $this->createForm(RiskFlagType::class, $enrolment, [
            RiskFlagType::USER_ROLES => $user->getRoles()
        ]);

        $flagForm->handleRequest($request);

        // Handle POST
        if ($flagForm->isSubmitted() && $flagForm->isValid()) {

            // Check if manual flag then handle.
            try {
                $manualFlagSubmit = $flagForm->get(RiskFlagType::MANUAL_FLAG_BUTTON);
            } catch (\OutOfBoundsException $e) {
                $manualFlagSubmit = null;
            }

            if ($manualFlagSubmit && $manualFlagSubmit->isClicked()) {
                $enrolment = $flagForm->getData();
                if ($user->isStudent()) {
                    $riskFlag = Enrolment::FLAG_BY_STUDENT;
                } else if ($user->isInstructor()) {
                    $riskFlag = Enrolment::FLAG_BY_INSTRUCTOR;
                } else {
                    throw new \LogicException("Invalid user type setting flag");
                }

                $enrolment->setRiskFlag($riskFlag, $flagForm->get(RiskFlagType::DESCRIPTION_INPUT)->getData());
            }

            // Check if remove flag then handle
            try {
                $removeFlagSubmit = $flagForm->get(RiskFlagType::REMOVE_FLAG_BUTTON);
            } catch (\OutOfBoundsException $e) {
                $removeFlagSubmit = null;
            }

            if ($removeFlagSubmit && $removeFlagSubmit->isClicked()) {
                $enrolment = $flagForm->getData();
                $enrolment->removeRiskFlag();
            }

            $this->entityManager->flush(); // Update db

            // Redirect to update form
            return $this->redirectToRoute(self::STUDENT_SUMMARY_PAGE, [
                'courseId' => $courseId,
                'instanceIndex' => $instanceIndex,
                'studentId' => $studentId
            ]);
        }

        // Create breadcrumbs
        $breadcrumbBuilder->addRoute('Courses', self::COURSES_PAGE);

        if ($user->isStudent()) {
            $breadcrumbBuilder
                ->add(strval($courseInstance));
        } else {
            $breadcrumbBuilder
                ->addRoute(strval($courseInstance), self::COURSE_SUMMARY_PAGE, [
                    'courseId' => $courseId,
                    'instanceIndex' => $instanceIndex
                ]);
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

    /**
     * @Route("/{courseId}/{instanceIndex}/lab/{labSlug}", name=CourseController::LAB_SUMMARY_PAGE)
     */
    public function viewLabSummary(
        Request $request,
        $instanceIndex,
        $courseId,
        $labSlug,
        LabRepository $labRepo,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {

        $courseInstance = $this->fetchCourseInstance($instanceIndex, $courseId);
        $lab = $this->fetchLab($labSlug, $courseInstance);

        // SECURITY
        // Only people who can edit this course instance are allowed to view (i.e. instructors on the course)
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // DANGER ZONE FORM
        $form = $this->createForm(LabDangerZoneType::class, $lab);
        $form->handleRequest($request);

        // Handle POST
        if ($form->isSubmitted() && $form->isValid()) {
            $updatedLab = $form->getData();
            $this->entityManager->persist($updatedLab);
            $this->entityManager->flush();
        }

        // Breadcrumbs
        $breadcrumbBuilder
            ->addRoute('Courses', self::COURSES_PAGE)
            ->addRoute(strval($courseInstance), self::COURSE_SUMMARY_PAGE, [
                'courseId' => $courseId,
                'instanceIndex' => $instanceIndex
            ])
            ->add($lab->getName());

        return $this->render('lab/lab_summary.html.twig', [
            'courseInstance' => $courseInstance,
            'lab' => $lab,
            'form' => $form->createView(),
            'labResponseRisks' => $labRepo->getLabResponseRisks($lab),
            'breadcrumbArray' => $breadcrumbBuilder->build()
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
        $instanceIndex,
        $courseId,
        $labSlug,
        $studentId,
        int $page = 1,
        LabResponseRepository $labResponseRepo,
        BreadcrumbBuilder $breadcrumbBuilder
    ) {
        $courseInstance = $this->fetchCourseInstance($instanceIndex, $courseId);
        $student = $this->fetchStudent($studentId);
        $lab = $this->fetchLab($labSlug, $courseInstance);

        // Check if question exists. Out of bounds exception means it doesn't.
        try {
            $question = $lab->getQuestions()->toArray()[$page - 1];
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('This lab survey question does not exist');
        }

        $labResponse = $labResponseRepo->findOneByLabAndStudent($lab, $student);

        // SECURITY
        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);
        // Check if the user is the owning student, otherwise deny
        $this->denyAccessUnlessGranted(StudentVoter::EDIT, $student);
        // Avoid jumping halfway into the form. Referrer must be a previous question.
        $this->checkValidLabSurveyReferrer($request, $page);

        // Create survey form
        $questionCount = $lab->getQuestionCount();
        $isLastQuestion = $page === $questionCount;

        $form = $this->generateLabSurveyForm($question, $labResponse, [
            'submitText' => $isLastQuestion ? 'Submit' : 'Next Question',
            'skipText' => $isLastQuestion ? 'Skip and Submit' : 'Skip Question',
        ]);

        $form->handleRequest($request);

        // Handle POST
        if ($form->isSubmitted()) {

            $skipped = $form->get(SurveyQuestionResponseType::SKIP_BUTTON_NAME)->isClicked();
            $isValid = $form->isValid();

            // If the form is skipped or is valid, redirect to the next page
            if ($skipped || $isValid) {

                // Persist to the database if the form was not skipped
                if (!$skipped  && $isValid) {
                    try {
                        $this->processLabSurveyFormData($form);
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

        $breadcrumbBuilder
            ->addRoute('Courses', self::COURSES_PAGE)
            ->add(strval($courseInstance))
            ->addRoute(strval($student), self::STUDENT_SUMMARY_PAGE, [
                'courseId' => $courseId,
                'instanceIndex' => $instanceIndex,
                'studentId' => $studentId
            ])
            ->add($lab->getName() . ' - Question ' . $page);

        // Render the form. If there are submission errors, they will be displayed too.
        return $this->render('lab/survey_page.html.twig', [
            'courseName' => $courseInstance->getName(),
            'labName' => $lab->getName(),
            'form' => $form->createView(),
            'questionNumber' => $page,
            'questionCount' => $questionCount,
            'breadcrumbArray' =>  $breadcrumbBuilder->build()
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

    private function processLabSurveyFormData(FormInterface $form)
    {
        $questionResponse = $form->getData();

        if ($questionResponse instanceof LabSentimentQuestionResponse) {

            // Make an API call
            $monkeyLearnApiKey = $this->getParameter('app.monkeylearn_api_key');
            $monkeyLearnModel = $this->getParameter("app.monkeylearn_model_id");
            $ml = new \MonkeyLearn\Client($monkeyLearnApiKey);
            $res = $ml->classifiers->classify($monkeyLearnModel, [$questionResponse->getText()]);
            // Parse the response
            $data = $res->result[0];

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

    private function fetchCourseInstance($index, $course)
    {
        /**
         * @var CourseInstanceRepository
         */
        $courseInstanceRepo = $this->entityManager->getRepository(CourseInstance::class);
        $courseInstance = $courseInstanceRepo->findByIndexAndCourseCode($index, $course);

        if (!$courseInstance) throw $this->createNotFoundException('This course does not exist');
        return $courseInstance;
    }

    private function fetchStudent($studentId)
    {
        /**
         * @var StudentRepository
         */
        $studentRepo =  $this->entityManager->getRepository(Student::class);
        $student = $studentRepo->find($studentId);

        if (!$student) throw $this->createNotFoundException('This student does not exist');
        return $student;
    }

    private function fetchLab($labSlug, $courseInstance)
    {
        /**
         * @var LabRepository
         */
        $labRepo = $this->entityManager->getRepository(Lab::class);

        $lab = $labRepo->findOneBy([
            "slug" => $labSlug,
            "courseInstance" => $courseInstance,
        ]);

        if (!$lab) throw $this->createNotFoundException('This lab does not exist in this course');
        return $lab;
    }
}
