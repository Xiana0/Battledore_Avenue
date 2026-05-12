<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'framework' => [
        'validation' => [
            'enable_attributes' => true,
            'email_validation_mode' => 'html5',
        ],
    ],
    // 'when@test' => [
    //     'framework' => [
    //         'validation' => [
    //             'not_compromised_password' => false,
    //         ],
    //     ],
    // ],
]);
