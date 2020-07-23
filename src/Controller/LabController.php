<?php

namespace App\Controller;

use App\Entity\Lab;
use App\Entity\Student;
use App\Entity\LabResponse;
use App\Entity\CourseInstance;
use App\Entity\LabXYQuestion;
use App\Form\Type\LabDangerZoneType;
use App\Security\Voter\StudentVoter;
use App\Entity\LabXYQuestionResponse;
use App\Entity\SurveyQuestionInterface;
use Symfony\Component\Form\FormInterface;
use App\Security\Voter\CourseInstanceVoter;
use App\Form\Type\LabXYQuestionResponseType;
use App\Form\Type\SurveyQuestionResponseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

/**
 * @Route("/courses")
 */
class LabController extends AbstractController
{
    /**
     * !page always generates the page number in the URL. Checks if page is a number.
     *
     * @Route("/{courseId}/{instanceId}/lab/{labId}/{studentId}/{!page}",
     *      name="lab_survey_response",
     *      requirements={"page"="\d+"}
     * )
     *
     */
    public function labSurvey(Request $request, $courseId, $instanceId, $labId, $studentId, int $page = 1)
    {
        // DATA
        $data = $this->getLabData($courseId, $instanceId, $labId);
        $courseInstance = $data['courseInstance'];
        $lab = $data['lab'];

        $student = $this->getDoctrine()
            ->getRepository(Student::class)
            ->find($studentId);

        if (!$student) {
            throw $this->createNotFoundException("This student does not exist");
        }

        // Check if question exists
        try {
            $question = $lab->getQuestions()->toArray()[$page - 1];
        } catch (\Throwable $th) {
            throw $this->createNotFoundException('This lab survey question does not exist');
        }

        // Response always exists.
        $labResponse = $this->getDoctrine()
            ->getRepository(LabResponse::class)
            ->findOneByLabAndStudent($lab, $student);

        // SECURITY

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);

        // Check if the user is the owning student, otherwise deny
        $this->denyAccessUnlessGranted(StudentVoter::EDIT, $student);

        // Avoid jumping halfway into the form. Referrer must be a previous question.
        $this->checkValidLabSurveyReferrer($request, $page);

        // FORM

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
                        'instanceId' => $instanceId,
                        'labId' => $labId,
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
                        'instanceId' => $instanceId,
                        'studentId' => $studentId
                    ]);
                }
            }
        }

        // Render the form. If there are submission errors, they will be displayed too.
        return $this->render('lab/survey_page.html.twig', [
            'course_name' => $courseInstance->getName(),
            'lab_name' => $lab->getName(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Includes course ID in the URL for readability.
     *
     * @Route("/{courseId}/{instanceId}/lab/{labId}/", name="lab_summmary")
     */
    public function labSummary(Request $request, $courseId, $instanceId, $labId)
    {
        // DATA
        $data = $this->getLabData($courseId, $instanceId, $labId);
        $courseInstance = $data['courseInstance'];
        $lab = $data['lab'];

        // SECURITY
        // Only people who can edit this course instance are allowed to view (i.e. instructors on the course)
        $this->denyAccessUnlessGranted(CourseInstanceVoter::EDIT, $courseInstance);

        // FORM
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

        return $this->render('lab/summary.html.twig', [
            'course_name' => $courseInstance->getName(),
            'lab_name' => $lab->getName(),
            'form' => $form->createView()
        ]);
    }

    private function getLabData($courseId, $instanceId, $labId)
    {
        $entityManager = $this->getDoctrine();

        $courseInstance = $entityManager
            ->getRepository(CourseInstance::class)
            ->find($instanceId);

        $lab = $entityManager
            ->getRepository(Lab::class)
            ->find($labId);

        if (!(ControllerUtils::coursePathExists($courseInstance, $courseId) && $lab)) {
            throw $this->createNotFoundException('This lab does not exist');
        }

        return [
            'courseInstance' => $courseInstance,
            'lab' => $lab,
        ];
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
