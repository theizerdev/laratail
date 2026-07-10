<?php

return [
    'enabled' => env('CSP_ENABLED', true),

    'policy' => [
        'default-src' => [
            'self',
        ],
        'script-src' => [
            'self',
            'unsafe-eval', // Necesario para Livewire
        ],
        'style-src' => [
            'self',
            'unsafe-inline', // A menudo necesario para estilos dinámicos
        ],
        'img-src' => [
            'self',
            'data:',
        ],
        'font-src' => [
            'self',
        ],
        'object-src' => [
            'none',
        ],
        'base-uri' => [
            'self',
        ],
        'form-action' => [
            'self',
        ],
        'frame-ancestors' => [
            'self',
        ],
    ],
];