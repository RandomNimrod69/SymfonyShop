<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('default/index.html.twig', [
            'message' => 'Welcome to the homepage!',
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about(CategoryRepository $categoryRepository): Response
    {
        return $this->render('default/about.html.twig', [
            'message' => 'Welcome to the about page!',
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/services", name="services")
     */
    public function services(CategoryRepository $categoryRepository): Response
    {
        return $this->render('default/services.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(CategoryRepository $categoryRepository): Response
    {
        return $this->render('default/contact.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('default/login.html.twig', [
            'categories' => $categories,
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/reset-password", name="reset_password")
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user) {
                $user->setPassword($passwordEncoder->encodePassword($user, $password));
                $em->flush();
                $this->addFlash('success', 'Password successfully reset! You can now login.');
                return $this->redirectToRoute('login');
            } else {
                $this->addFlash('error', 'Email not found.');
            }
        }

        return $this->render('default/reset_password.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em, CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('Register', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Check if email already exists
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('error', 'This email is already registered.');
                return $this->redirectToRoute('register');
            }

            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(false);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Registration successful! You can now login.');
            return $this->redirectToRoute('login');
        }

        return $this->render('default/register.html.twig', [
            'categories' => $categories,
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/category", name="category_show")
     */
    public function category(Request $request, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $id = $request->query->get('id');
        $category = null;
        $products = [];

        if ($id) {
            $category = $categoryRepository->find($id);
            if (!$category) {
                throw $this->createNotFoundException('Category not found');
            }
            $products = $productRepository->findByCategory((int)$id);
        }

        return $this->render('default/category.html.twig', [
            'categories' => $categories,
            'category' => $category,
            'products' => $products,
        ]);
    }
}
