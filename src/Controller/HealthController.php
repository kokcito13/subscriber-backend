<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    #[Route('/', name: 'app_health', methods: ['GET'])]
    public function index(Connection $connection): JsonResponse
    {
        $now = (new \DateTimeImmutable())->format(\DateTime::ATOM);

        $checks = [
            'app' => ['status' => 'ok'],
        ];

        $status = 'ok';
        $httpStatus = JsonResponse::HTTP_OK;

        try {
            // Try to connect and run a lightweight query to verify DB readiness
            $connection->connect();
            $connection->executeQuery('SELECT 1')->fetchOne();

            $checks['database'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            // On any failure mark database as error and return 503
            $checks['database'] = [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];

            $status = 'error';
            $httpStatus = JsonResponse::HTTP_SERVICE_UNAVAILABLE;
        }

        $payload = [
            'status' => $status,
            'time' => $now,
            'checks' => $checks,
        ];

        return new JsonResponse($payload, $httpStatus);
    }
}
