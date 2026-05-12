<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'lexik_jwt_authentication' => [

        // Token encoding/decoding settings
        'encoder' => [
            // Encryption algorithm used by the encoder service
            'signature_algorithm' => '%env(JWT_ALGORITHM)%',
        ],

        // SSL keys
        'secret_key' => '%env(resolve:JWT_SECRET_KEY)%', // Required for token creation
        'public_key' => '%env(resolve:JWT_PUBLIC_KEY)%', // Required for token verification
        'pass_phrase' => null, # required for token creation

        // Remove token from body when cookies used
        'remove_token_from_body_when_cookies_used' => true,

        // Token TTL in seconds
        'token_ttl' => '%env(JWT_EXPIRY_TIME)%',

        // Clock skew in seconds
        'clock_skew' => '%env(int:JWT_CLOCK_SKEW)%',

        // Token extraction settings
        'token_extractors' => [
            // Look for a token as Authorization Header
            'authorization_header' => [
                'enabled' => true,
                'prefix' => 'Bearer',
                'name' => '%env(JWT_AUTH_HEADER)%',
            ],
            'cookie' => [
                'enabled' => true,
                'name' => '%env(JWT_COOKIE_NAME)%',
            ],
        ],
    ],
]);
