<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

/**
 * Two factor authenticating token
 *
 * Note: Only the user name is verified, user from token is awaiting 2nd factor authentication.
 */
class TwoFactorAuthenticatingToken extends PreAuthenticatedToken
{
}
