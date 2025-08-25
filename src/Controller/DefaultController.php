<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'message' => 'Welcome to the homepage!',
        ]);
    }

    #[Route('/test/{firstname}/{lastname}', name: 'test')]
    public function test(string $firstname, string $lastname): Response
    {
        $name = $firstname . ' ' . $lastname;
        return $this->render('default/test.html.twig', ['name' => $name]);
    }
}
