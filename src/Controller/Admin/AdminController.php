<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
    private $users,$posts,$comments,$tags;

    public function __construct(
                                UserRepository $users,
                                PostRepository $posts,
                                CommentRepository $comments,
                                TagRepository $tags)
    {
        $this->users=$users;
        $this->posts=$posts;
        $this->comments=$comments;
        $this->tags=$tags;
    }

    /**
     * @Route("/", methods={"GET"}, name="dashboard_index")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('dashboard/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'posts' => $this->posts->findAll(),
            'latestPosts' => $this->posts->findLatest(),
            'comments' => $this->comments->findAll(),
            'latestComments' => $this->comments->findLatest(),
            'users' => $this->users->findAll(),
            'tags' => $this->tags->findAll()
        ]);
    }

    /**
     * @Route("/posts", methods={"GET"}, name="dashboard_posts_index")
     *
     * @return Response
     */

    public function blog(): Response
    {

        return $this->render('dashboard/posts/posts.html.twig', ['posts' => $this->posts->findAll()]);
    }

    /**
     * @Route("/new", methods={"GET", "POST"}, name="dashboard_posts_new")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $post->setAuthor($this->getUser());

        // See https://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm(PostType::class, $post)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(Slugger::slugify($post->getTitle()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'post.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('dashboard_posts_new');
            }

            return $this->redirectToRoute('dashboard_posts_index');
        }

        return $this->render('dashboard/posts/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{username}/post/{id<\d+>}", methods={"GET"}, name="dashboard_posts_show")
     *
     * @return Response
     */
    public function show(Post $post): Response
    {
        return $this->render('dashboard/posts/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/{username}/post/{id<\d+>}/edit",methods={"GET", "POST"}, name="dashboard_posts_edit")
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(Slugger::slugify($post->getTitle()));
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('dashboard_posts_edit', ['id' => $post->getId()]);
        }

        return $this->render('dashboard/posts/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{username}/post/{id}/delete", methods={"POST"}, name="dashboard_posts_delete")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function delete(Request $request, Post $post): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('dashboard_posts_index');
        }

        $post->getTags()->clear();

        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'post.deleted_successfully');

        return $this->redirectToRoute('dashboard_posts_index');
    }
}
