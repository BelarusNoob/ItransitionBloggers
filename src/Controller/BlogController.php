<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Tag;
use App\Events;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class BlogController extends AbstractController
{
    private $eventDispatcher, $posts, $tags;

    public function __construct(EventDispatcherInterface $eventDispatcher, PostRepository $posts, TagRepository $tags)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->posts = $posts;
        $this->tags = $tags;
    }

    /**
     * @Route("/", defaults={"page": "1", "_format"="html"}, methods={"GET"}, name="homePage")
     * @Route("/page/{page<[1-9]\d*>}", defaults={"_format"="html"}, methods={"GET"}, name="blog_index_paginated")
     * @Cache(smaxage="10")
     *
     * @param Request $request
     * @param int $page
     * @param string $_format
     *
     * @return Response
     */
    public function index(Request $request, int $page, string $_format): Response
    {
        $Tags = $this->getDoctrine()->getManager()->getRepository(Tag::class)->findAll();

        $tag = null;
        if ($request->query->has('tag')) {
            $tag = $this->tags->findOneBy(['name' => $request->query->get('tag')]);
        }
        $latestPosts = $this->posts->findLatest($page, $tag);

        return $this->render('homepage/homepage.'.$_format.'.twig', [
            'posts' => $latestPosts,
            'Tags' => $Tags,
        ]);
    }

    /**
     * @Route("{username}/post/{slug}", methods={"GET"}, name="blog_post")
     *
     * @param Post $post
     *
     * @return Response
     */
    public function postShow(Post $post): Response
    {
        return $this->render('blog/post_show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/comment/{postSlug}/new", methods={"POST"}, name="comment_new")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @ParamConverter("post", options={"mapping": {"postSlug": "slug"}})
     *
     * @param Request $request
     * @param Post $post
     *
     * @return Response
     */
    public function commentNew(Request $request, Post $post): Response
    {
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $post->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $event = new GenericEvent($comment);

            $this->eventDispatcher->dispatch(Events::COMMENT_CREATED, $event);

            return $this->redirectToRoute('blog_post', ['slug' => $post->getSlug(),'username' => $post->getAuthor()->getUsername()]);
        }

        return $this->render('blog/comment_form_error.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Post $post
     *
     * @return Response
     */
    public function commentForm(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('blog/_comment_form.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/search", methods={"GET"}, name="blog_search")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $foundPosts = $this->posts->findBySearchQuery($query, 10);

        return $this->render('blog/search.html.twig', [
            'posts' => $foundPosts,
        ]);
    }
}

