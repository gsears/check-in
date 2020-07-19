<?php

namespace App\Controller;

use App\Security\Roles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    /**
     * @Route("/courses", name="courses")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted(Roles::LOGGED_IN);

        return $this->render('course/index.html.twig', [
            'controller_name' => 'CourseController',
        ]);
    }
}
