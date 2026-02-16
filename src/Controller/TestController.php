<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function test(LoggerInterface $logger): JsonResponse
    {
        $logger->info('Test endpoint called', ['path' => '/test']);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Symfony application is working!',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
