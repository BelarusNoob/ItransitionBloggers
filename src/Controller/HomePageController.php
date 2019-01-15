<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    /**
     * @Route("/", name="homePage")
     */
    public function index()
    {
        $Posts = $this->getDoctrine()->getManager()->getRepository(Post::class)->findAll();
        $Tags = $this->getDoctrine()->getManager()->getRepository(Tag::class)->findAll();

        return $this->render('homepage/homepage.html.twig', [
            'Posts' => $Posts,
            'Tags' => $Tags,
        ]);
    }
}
