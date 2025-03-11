<?php

// |============================================|
// | Documentação da API com Swagger            |
// |============================================|

namespace App;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        title: 'Easy Kanban API',
        version: '1.0',
        description: 'This is a Kanban Project Management API',
    ),
    servers: [
        new OA\Server(
            url: 'http://localhost:8000',
            description: 'Easy Kanban API development environment'
        )
    ],
    security: [['bearerAuth' => []]],
    tags: [
        new OA\Tag(
            name: 'Kanban',
            description: 'Kanban as a agile methodology'
        ),
        new OA\Tag(
            name: 'Project Management',
            description: 'Fast, agile, simple, and easy management'
        ),
    ],
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    description: 'Basic Auth'
)]
class OpenApi {}
