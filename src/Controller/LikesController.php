<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/likes")
 */
class LikesController extends AbstractController
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
     * @Route("/like/{id}", name="likes_like")
     */
    public function like(Post $post)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $post->like($currentUser);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'count' => $post->getLikedBy()->count()
        ]);
    }

    /**
     * @Route("/unlike/{id}", name="likes_unlike")
     */
    public function unlike(Post $post)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $post->getLikedBy()->removeElement($currentUser);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'count' => $post->getLikedBy()->count()
        ]);
    }


    public function popular(): Response
    {
        return $this->render('blog/popular.html.twig', [
            'likedPosts' => $this->posts->findPopular()
        ]);
    }
}
