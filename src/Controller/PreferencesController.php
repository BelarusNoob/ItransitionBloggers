<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/preferences")
 */
class PreferencesController extends AbstractController
{
    private $eventDispatcher, $posts, $tags;

    public function __construct(EventDispatcherInterface $eventDispatcher, PostRepository $posts, TagRepository $tags)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->posts = $posts;
        $this->tags = $tags;
    }

    /**
     * @Route("/", name="blog_preferences")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function preferences(Request $request): Response
    {
        $Tags = $this->getDoctrine()->getManager()->getRepository(Tag::class)->findAll();

        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $this->tags->findOneBy(['name' => $request->query->get('tag')]);
        }
        $all_following_posts = $this->getUser()->getFollowing();
        $res = [];
        for ($i=0; $i<count($all_following_posts); $i++) {
            $res = $all_following_posts[$i]->getPosts();
        }

        return $this->render('blog/preferences.html.twig', [
            'Tags' => $Tags,
            'following_posts' => $res,
        ]);
    }

}