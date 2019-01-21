<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class SecurityController extends AbstractController
{
    private $passwordEncoder;
    private $helper;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, AuthenticationUtils $helper)
    {
        $this->passwordEncoder=$passwordEncoder;
        $this->helper=$helper;
    }

    /**
     * @Route("/register", name="userRegistration")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function register(Request $request): Response
    {
        $user = new User();

        $user->setImage(
            new File($this->getParameter('images_directory').'/'.'user.png')
        );

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $file = $user->getImage();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $fileName
                );
            } catch (FileException $e) {
            }

            $user->setImage($fileName);

            if ($form->get('IsBlogger')->getData()==true)
            {
                $user->setRoles(
                    ['ROLE_BLOGGER']
                );
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }


    /**
     * @Route("/login", name="login")
     */
    public function login(): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $this->helper->getLastUsername(),
            'error' => $this->helper->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
