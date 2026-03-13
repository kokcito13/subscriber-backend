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
            'app_name' => 'Cyla',
        ]);
    }

    #[Route('/privacy-policy', name: 'subscriber_privacy_policy', methods: ['GET'])]
    public function privacyPolicy(): Response
    {
        return $this->render('subscriber/privacy_policy.html.twig', [
            'app_name'      => 'Cyla',
            'last_updated'  => 'March 13, 2026',
            'contact_email' => 'privacy@cyla.app',
        ]);
    }
}
