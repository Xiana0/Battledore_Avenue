<?php

/**
 * SAML2 Provider
 * Copyright (c) e.World Technology Limited. All rights reserved.
 */

namespace PHPMaker2026\Project1;

use Psr\Log\LoggerInterface;
use LightSaml\Binding\AbstractBinding;
use LightSaml\Binding\BindingFactory;
use LightSaml\Context\Profile\MessageContext;
use LightSaml\Model\Assertion\Issuer;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Model\Metadata\IdpSsoDescriptor;
use LightSaml\Model\Metadata\SingleSignOnService;
use LightSaml\Model\Metadata\SingleLogoutService;
use LightSaml\Model\Protocol\AuthnRequest;
use LightSaml\Model\Protocol\Response;
use LightSaml\Model\Protocol\LogoutRequest;
use LightSaml\Model\Protocol\SamlMessage;
use LightSaml\Model\Protocol\Status;
use LightSaml\Model\Protocol\StatusCode;
use LightSaml\Model\Protocol\StatusResponse;
use LightSaml\Model\XmlDSig\SignatureWriter;
use LightSaml\SamlConstants;
use LightSaml\Helper;
use LightSaml\State\Sso\SsoSessionState;
use LightSaml\Store\Sso\SsoStateStoreInterface;
use LightSaml\Resolver\Session\SessionProcessorInterface;
use LightSaml\Error\LightSamlAuthenticationException;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\KeyHelper;
use LightSaml\Binding\SamlPostResponse;
use Dflydev\DotAccessData\Data;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use ReflectionClass;
use ReflectionProperty;
use DateTime;
use Exception;
use InvalidArgumentException;

/**
 * SAML 2 service
 */
class Saml2
{
    protected Data $config;

    /**
     * Attribute map (attribute name => property name of AccessTokenUser)
     */
    public static array $attributeMap = [
        'displayname' => 'displayName',
        'givenname' => 'firstName',
        'surname' => 'lastName',
        'emailaddress' => 'email',
        'mail' => 'email',
    ];

    /**
     * Provider ID
     */
    public string $providerId = 'saml';

    /**
     * Entity ID
     */
    public string $entityId = '';

    /**
     * X.509 certificate
     */
    public string $certificate = '';

    /**
     * Private key
     */
    protected string $privateKey = '';

    /**
     * Authorization Endpoint
     */
    protected string $authorizeUrl = '';

    /**
     * Redirection Endpoint or Callback
     */
    protected string $callback = '';

    /**
     * Attributes
     */
    protected ?array $attributes = null;

    /**
     * IdP Entity Descriptor
     */
    protected ?EntityDescriptor $idpEntityDescriptor = null;

    /**
     * Binding for Single logout service
     */
    public static ?string $singleSignOnBinding = null;

    /**
     * Single logout service enabled
     */
    public static bool $singleLogoutServiceEnabled = true;

    /**
     * Constructor
     */
    public function __construct(
        protected SsoStateStoreInterface $ssoStateStore, // SSO state store
        protected SessionProcessorInterface $sessionProcessor, // Session processor
        protected LoggerInterface $logger,
        protected RequestStack $requestStack,
        protected array $options,
    ) {
        $this->config = new Data($options);
        $this->setCallback(FullUrl(PathJoin(BasePath(), $this->config->get('callback'))));
        $this->configure();
        if (IsDebug()) {
            $this->logger->debug(sprintf('Initialize %s, config: ', get_class($this)), $options);
        }
    }

    /**
     * Configure SAML
     */
    protected function configure(): void
    {
        $this->entityId = $this->config->get('entityId') ?: FullUrl(GetApiUrl(Config('API_METADATA_ACTION')));
        $this->certificate = $this->config->get('certificate');
        $this->privateKey = $this->config->get('privateKey');
        $this->idpEntityDescriptor = EntityDescriptor::load($this->config->get('idpMetadata'));
        self::$singleSignOnBinding ??= ContainsText($this->config->get('idpMetadata'), 'login.microsoftonline.com')
            ? SamlConstants::BINDING_SAML2_HTTP_POST // Use POST for Azure
            : SamlConstants::BINDING_SAML2_HTTP_REDIRECT;
    }

    /**
     * Get logger
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Set Adapter's API callback url
     *
     * @param string $callback
     *
     * @throws InvalidArgumentException
     */
    protected function setCallback($callback)
    {
        if (!filter_var($callback, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('A valid callback url is required.');
        }
        $this->callback = $callback;
    }

    /**
     * Get SAML response
     *
     * @param Request $request
     * @return StatusResponse
     */
    public function getSamlResponse(Request $request): StatusResponse
    {
        $bindingFactory = new BindingFactory();
        $binding = $bindingFactory->getBindingByRequest($request);
        $messageContext = new MessageContext();
        $binding->receive($request, $messageContext);
        return $messageContext->getMessage();
    }

    /**
     * Get value from request (query or request)
     *
     * @param Request $request
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get(Request $request, string $key, mixed $default = null): mixed
    {
        if ($request->query->has($key)) {
            return $request->query->all()[$key];
        }
        if ($request->request->has($key)) {
            return $request->request->all()[$key];
        }
        return $default;
    }

    /**
     * Authenticate
     */
    public function authenticate(): void
    {
        $this->logger->info(sprintf('%s::authenticate()', $this::class));
        if ($this->isConnected()) {
            return;
        }
        $request = $this->requestStack->getCurrentRequest();
        if (!$this->get($request, "SAMLResponse")) {
            return;
        }
        $response = $this->getSamlResponse($request); // \LightSaml\Model\Protocol\Response
        if (!$response->getStatus() || !$response->getStatus()->isSuccess()) {
            $this->checkStatusResponse($response);
            return;
        }
        $this->logger->info(sprintf('%s::authentication succeeded', $this::class));

        // Get assertions
        $assertions = $response->getAllAssertions();

        // Process assertions and set SSO state
        $this->sessionProcessor->processAssertions(
            $assertions,
            $this->entityId,
            $this->idpEntityDescriptor->getEntityID()
        );
        $this->attributes = [];
        foreach ($assertions as $assertion) {
            foreach ($assertion->getAllAttributeStatements() as $attributeStatement) {
                foreach ($attributeStatement->getAllAttributes() as $attribute) {
                    $name = $attribute->getName();
                    if (StartsString('http://', $name)) {
                        $parts = explode('/', $name);
                        $name = array_pop($parts);
                    }
                    $this->attributes[$name] = $attribute->getFirstAttributeValue();
                }
            }
        }
    }

    /**
     * Is connected
     */
    public function isConnected(): bool
    {
        return $this->ssoStateStore->getSpSession($this->idpEntityDescriptor->getEntityID()) !== null;
    }

    /**
     * Check StatusResponse
     *
     * @param StatusResponse $response
     *
     * @return void
     */
    protected function checkStatusResponse($response): void
    {
        $status = $response->getStatus();
        if ($status === null) {
            $message = 'Status response does not have Status set';
            $this->logger->error($message);
            if ($response instanceof Response) {
                throw new LightSamlAuthenticationException($response, $message);
            } else {
                throw new LightSamlException($message);
            }
        }
        $message = $status->getStatusCode()->getValue() . "\n" . $status->getStatusMessage();
        if ($status->getStatusCode()->getStatusCode()) {
            $message .= "\n" . $status->getStatusCode()->getStatusCode()->getValue();
        }
        if (trim($message) !== '') {
            $message = 'Unsuccessful SAML response: ' . $message;
            $this->logger->error($message);
            if ($response instanceof Response) {
                throw new LightSamlAuthenticationException($response, $message);
            } else {
                throw new LightSamlException($message);
            }
        }
    }

    /**
     * Initiate the authorization process
     */
    public function authenticateBegin(): SymfonyResponse
    {
        $response = $this->getAuthorizeResponse();
        if ($response instanceof RedirectResponse) { // Redirect
            if (IsDebug()) {
                $this->logger->debug(sprintf('%s::authenticateBegin(), redirecting user to:', $this::class), [$response->getTargetUrl()]);
            }
            return $response;
        } elseif ($response instanceof SamlPostResponse) { // Post
            $content = '<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="utf-8">
                    <script%s>document.addEventListener("DOMContentLoaded", () => document.getElementById("samlSubmit").submit());</script>
                </head>
                <body>
                    <form id="samlSubmit" method="post" action="%s">
                        %s
                    </form>
                </body>
            </html>';
            $fields = '';
            foreach ($response->getData() as $name => $value) {
                $fields .= sprintf('<input type="hidden" name="%s" value="%s" />', HtmlEncode($name), HtmlEncode($value));
            }
            $content = sprintf($content, Nonce(), HtmlEncode($response->getDestination() ?? ''), $fields);
            return new SymfonyResponse($content);
        }
    }

    /**
     * Build Authorization response for authorization request
     *
     * @param array $parameters
     *
     * @return SymfonyResponse
     */
    protected function getAuthorizeResponse(array $parameters = []): SymfonyResponse
    {
        $idpSsoDescriptor = $this->idpEntityDescriptor->getFirstIdpSsoDescriptor();
        $sso = $idpSsoDescriptor->getFirstSingleSignOnService(self::$singleSignOnBinding);
        $wantAuthnRequestsSigned = $idpSsoDescriptor->getWantAuthnRequestsSigned();
        $authnRequest = new AuthnRequest();
        $authnRequest->setAssertionConsumerServiceURL($this->callback)
            ->setProtocolBinding(self::$singleSignOnBinding)
            ->setID(Helper::generateID())
            ->setIssueInstant(new DateTime())
            ->setDestination($sso->getLocation())
            ->setIssuer(new Issuer($this->entityId));
        $bindingFactory = new BindingFactory();
        $binding = $bindingFactory->create(self::$singleSignOnBinding);
        $messageContext = new MessageContext();
        $messageContext->setMessage($authnRequest);
        if ($wantAuthnRequestsSigned && $this->certificate && $this->privateKey) {
            $certificate = X509Certificate::fromFile(ServerMapPath($this->certificate, true));
            $privateKey = KeyHelper::createPrivateKey(ServerMapPath($this->privateKey, true), '', true); // Private key is file
            $signature = new SignatureWriter($certificate, $privateKey);
            $authnRequest->setSignature($signature);
            if (IsDebug()) {
                $this->logger->debug(sprintf('Message signed with fingerprint "%s"', $signature->getCertificate()->getFingerprint()));
            }
        } else {
            if (IsDebug()) {
                $this->logger->debug('Signing disabled');
            }
        }
        return $binding->send($messageContext);
    }

    /**
     * Disconnect (Logout)
     *
     * @return void
     */
    public function disconnect(): void
    {
        if (!self::$singleLogoutServiceEnabled) {
            return;
        }
        $request = $this->requestStack->getCurrentRequest();
        if (!$this->get($request, 'SAMLResponse')) { // Send logout request to IdP
            $idpSsoDescriptor = $this->idpEntityDescriptor?->getFirstIdpSsoDescriptor();
            if (!$idpSsoDescriptor) {
                return;
            }
            $slo = $idpSsoDescriptor->getFirstSingleLogoutService();
            if (!$slo) {
                return;
            }
            $session = $this->ssoStateStore->getSpSession($this->idpEntityDescriptor->getEntityID());
            if (!$session) {
                return;
            }
            $logoutRequest = new LogoutRequest();
            $logoutRequest
                ->setSessionIndex($session->getSessionIndex())
                ->setNameID(new NameID($session->getNameId(), $session->getNameIdFormat()))
                ->setDestination($slo->getLocation())
                ->setID(Helper::generateID())
                ->setIssueInstant(new DateTime())
                ->setIssuer(new Issuer($this->entityId));
            $bindingFactory = new BindingFactory();
            $redirectBinding = $bindingFactory->create(SamlConstants::BINDING_SAML2_HTTP_REDIRECT); // Assume HTTP-Redirect
            $messageContext = new MessageContext();
            $messageContext->setMessage($logoutRequest);
            $response = $redirectBinding->send($messageContext); // RedirectResponse
            header(sprintf('Location: %s', $response->getTargetUrl())); // Redirect immediately
            exit(1);
        } else { // Logout response from IdP
            $response = $this->getSamlResponse($request); // \LightSaml\Model\Protocol\LogoutResponse
            if ($response->getStatus()?->isSuccess()) { // Success
                $this->ssoStateStore->terminateSession($this->idpEntityDescriptor->getEntityID()); // Terminate session
                return;
            }
            $this->checkStatusResponse($response); // Failure
        }
    }

    /**
     * Get user
     *
     * @return ?AccessTokenUser
     */
    public function getUser(): ?AccessTokenUser
    {
        $attributes = $this->attributes;
        if (empty($attributes)) {
            return null;
        }

        // Find the user identifier
        if (!isset($attributes['userIdentifier'])) {
            $userIdentifier = $attributes[Config('EXTERNAL_LOGIN_PROVIDERS.saml.identifyingAttribute') ?? null]
                ?? $attributes['eduPersonPrincipalName']
                ?? $attributes['eduPersonTargetedID']
                ?? $attributes['userprincipalname']
                ?? $attributes['emailaddress']
                ?? $attributes['email']
                ?? $attributes['mail']
                ?? $attributes['objectidentifier'] // Microsoft
                ?? null;
            if ($userIdentifier) {
                $attributes['userIdentifier'] ??= $userIdentifier;
            } else {
                if (IsDebug()) {
                    $this->logger->debug('Missing user identifier in SAML attributes', $attributes);
                }
                throw new Exception('Missing user identifier, please set the config setting "identifyingAttribute" as the attribute name of the user identifier.');
            }
        }

        // Map attributes to properties
        $newAttributes = [];
        foreach ($attributes as $key => $value) {
            $newKey = self::$attributeMap[$key] ?? $key;
            $newAttributes[$newKey] = $value;
        }

        // Create the user
        return new AccessTokenUser(...$newAttributes);
    }
}
