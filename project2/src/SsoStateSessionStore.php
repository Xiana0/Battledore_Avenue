<?php

namespace PHPMaker2026\Project1;

use LightSaml\State\Sso\SsoState;
use LightSaml\Store\Sso\SsoStateStoreInterface;
use LightSaml\State\Sso\SsoSessionState;
use Symfony\Component\HttpFoundation\RequestStack;

class SsoStateSessionStore implements SsoStateStoreInterface
{
    /**
     * Constructor
     *
     * @param RequestStack $requestStack
     * @param string $key
     */
    public function __construct(
        protected RequestStack $requestStack,
        protected string $key,
    ) {
    }

    /**
     * Get SSO state
     *
     * @return SsoState
     */
    public function get()
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $result = $session->get($this->key);
        if (null == $result) {
            $result = new SsoState();
            $this->set($result);
        }
        return $result;
    }

    /**
     * Set SSO state
     *
     * @param SsoState $ssoState
     * @return void
     */
    public function set(SsoState $ssoState)
    {
        $session = $this->requestStack->getCurrentRequest()->getSession();
        $ssoState->setLocalSessionId($session->getId());
        $session->set($this->key, $ssoState);
    }

    /**
     * Terminate session by IdP entity ID
     *
     * @param string $entityId IdP entity ID
     * @return int Number of terminated sessions
     */
    public function terminateSession($entityId)
    {
        $ssoState = $this->get();
        $count = 0;
        $ssoState->modify(function (SsoSessionState $session) use ($entityId, &$count) {
            if ($session->getIdpEntityId() == $entityId) {
                ++$count;
                return false;
            }
            return true;
        });
        $this->set($ssoState);
        return $count;
    }

    /**
     * Get SP session by IdP entity ID
     *
     * @param string $entityId IdP entity ID
     * @return ?SsoSessionState
     */
    public function getSpSession($entityId)
    {
        $ssoState = $this->get();
        $spSessions = $ssoState->filter($entityId, null, null, null, null);
        return array_shift($spSessions);
    }
}
