<?php

namespace App\Controller\Subscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController
{
    #[Route('/', name: 'subscriber_home', methods: ['GET'])]
    public function home(): Response
    {
        return new Response(
            <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Subscriber</title>
            </head>
            <body>
                <h1>Welcome to Subscriber</h1>
            </body>
            </html>
            HTML,
            Response::HTTP_OK,
            ['Content-Type' => 'text/html']
        );
    }
}
