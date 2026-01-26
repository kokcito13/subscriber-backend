<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function test(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Symfony application is working!',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
