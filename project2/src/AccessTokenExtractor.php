<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessToken\AccessTokenExtractorInterface;

/**
 * Access token extractor for SAML
 */
class AccessTokenExtractor implements AccessTokenExtractorInterface
{

    public function extractAccessToken(Request $request): ?string
    {
        if ($request->attributes->get("_route") == 'saml_login') {
            $key = 'SAMLResponse';
            if ($request->query->has($key)) {
                return $request->query->all()[$key];
            }
            if ($request->request->has($key)) {
                return $request->request->all()[$key];
            }
        }
        return null;
    }
}
