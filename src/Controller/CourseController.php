<?php

namespace App\Controller;

use App\Entity\CourseInstance;
use App\Entity\User;
use App\Security\Roles;
use App\Security\Voter\CourseInstanceVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        }

        if($user->isInstructor()) {
            $courseInstances = $this->getDoctrine()
                ->getRepository(CourseInstance::class)
                ->findByInstructor($user->getInstructor());
        }

        return $this->render('course/index.html.twig', [
            'courseInstances' => $courseInstances,
        ]);
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

        // ...and courseid matches up with the instance id
        $courseCode = $courseInstance->getCourse()->getCode();

        if(!$courseInstance || $courseCode !== $courseId) {
            throw $this->createNotFoundException('This course does not exist');
        }

        // Check permission to view course instance
        $this->denyAccessUnlessGranted(CourseInstanceVoter::VIEW, $courseInstance);

        // renders templates/lucky/number.html.twig
        return $this->render('course/view.html.twig', ['courseInstance' => $courseInstance]);
    }
}
