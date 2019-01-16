<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('blog/about.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
