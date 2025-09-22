<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordResetController extends AbstractController
{
    #[Route('/reset-password', name: 'reset_password')]
    public function reset(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $em
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $newPassword = $request->request->get('password');

            // Find user by email
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'No account found with that email.');
                return $this->redirectToRoute('reset_password');
            }

            // Encode the new password
            $hashedPassword = $encoder->encodePassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // Save changes
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Password successfully reset. You can now log in.');
            return $this->redirectToRoute('login');
        }

        // Render reset form
        return $this->render('security/reset_password.html.twig');
    }
}
