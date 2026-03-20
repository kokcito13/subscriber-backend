<?php

namespace App\Controller\BabyRhythm;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'baby_rhythm_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('baby-rhythm/landing/index.html.twig', [
            'app_name'     => 'Baby Rhythm',
            'app_tagline'  => 'Understand your baby\'s natural rhythm',
            'download_url' => '#',
        ]);
    }
}
