<?php

/*
LabSurveyPageController.php
Gareth Sears - 2493194S
*/

namespace App\Controller;

use App\Entity\LabResponse;
use App\Entity\LabXYQuestion;
use App\Service\BreadcrumbBuilder;
use App\Entity\LabSentimentQuestion;
use App\Security\Voter\StudentVoter;
use App\Entity\LabXYQuestionResponse;
use App\Entity\SurveyQuestionInterface;
use App\Repository\LabResponseRepository;
use Symfony\Component\Form\FormInterface;
use App\Security\Voter\CourseInstanceVoter;
use App\Entity\LabSentimentQuestionResponse;
use App\Form\Type\LabXYQuestionResponseType;
use App\Form\Type\SurveyQuestionResponseType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Type\LabSentimentQuestionResponseType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LabSurveyPageController extends AbstractCourseController
{
    const ROUTE = 'lab_survey_page';

    /**
     * !page always generates the page number in the URL. Checks if page is a number.
     *
     * @Route("/courses/{courseId}/{instanceIndex}/lab/{labSlug}/{studentId}/survey/{!page}",
     *      name=LabSurveyPageController::ROUTE,
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
        } catch (\Exception $e) {
            throw $this->createNotFoundException('This lab survey question does not exist');
        }

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
        $labResponse = $labResponseRepo->findOneByLabAndStudent($lab, $student);

        if ($labResponse->getSubmitted()) {
            throw $this->createAccessDeniedException('Cannot resubmit a lab survey.');
        }

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
            // If invalid, the page will regenerate showing errors.
            // A skipped form is technically invalid, which is why it is passed through here.
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
                    return $this->redirectToRoute(
                        StudentSummaryPageController::ROUTE,
                        [
                            'courseId' => $courseId,
                            'instanceIndex' => $instanceIndex,
                            'studentId' => $studentId
                        ]
                    );
                } else {
                    // Get next page in the survey
                    return $this->redirectToRoute(self::ROUTE, [
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
            ->addRoute('Courses', CoursesPageController::ROUTE)
            ->add(strval($courseInstance))
            ->addRoute(strval($student), StudentSummaryPageController::ROUTE, [
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
}
