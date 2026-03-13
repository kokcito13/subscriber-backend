<?php

namespace App\Controller\Subscriber;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'subscriber_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('subscriber/index.html.twig', [
            'app_name' => 'SubTracker',
        ]);
    }
}
