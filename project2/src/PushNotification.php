<?php

namespace PHPMaker2026\Project1;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Push Notification class
 */
class PushNotification
{
    protected Request $request;

    // Constructor
    public function __construct(
        protected RequestStack $requestStack,
        protected ManagerRegistry $registry,
        protected AppServiceLocator $locator,
        protected WebPush $webPush,
    ) {
        $this->request = $this->requestStack->getCurrentRequest();
    }

    public function subscribe(): Response
    {
        return $this->addSubscription($this->request->request->all());
    }

    public function send(?array $entities): Response
    {
        $tokenIdKey = Config("CSRF_TOKEN.id_key"); // "_csrf_id"
        $tokenValueKey = Config("CSRF_TOKEN.value_key"); // "_csrf_token"
        $this->request->request->remove("key_m"); // Remove 'key_m' from POST parameters
        $payload = $this->request->request->all(); // Get all post data
        if ($tokenIdKey && isset($payload[$tokenIdKey])) { // Remove CSRF token ID
            unset($payload[$tokenIdKey]);
        }
        if ($tokenValueKey && isset($payload[$tokenValueKey])) { // Remove CSRF token value
            unset($payload[$tokenValueKey]);
        }
        if (!$entities || count($entities) == 0) {
            return new JsonResponse([]);
        }
        if (Config("SEND_PUSH_NOTIFICATION_TIME_LIMIT") >= 0) {
            @set_time_limit(Config("SEND_PUSH_NOTIFICATION_TIME_LIMIT")); // Set time limit for sending push notification
        }
        return $this->sendNotifications($entities, $payload);
    }

    public function delete(): Response
    {
        return $this->deleteSubscription($this->request->request->all());
    }

    protected function addSubscription(array $subscription): Response
    {
        $user = CurrentUserID() ?? CurrentUserName();
        $endpoint = $subscription["endpoint"] ?? "";
        $publicKey = $subscription["publicKey"] ?? "";
        $authToken = $subscription["authToken"] ?? "";
        $contentEncoding = $subscription["contentEncoding"] ?? "";
        if (
            IsEmpty(Config("SUBSCRIPTION_TABLE_NAME"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_USER"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING"))
            || IsEmpty($endpoint)
            || IsEmpty($publicKey)
            || IsEmpty($authToken)
            || IsEmpty($contentEncoding)
        ) {
            return new JsonResponse(["success" => false]);
        }
        $row = [
            Config("SUBSCRIPTION_FIELD_NAME_USER") => $user,
            Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT") => $endpoint,
            Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY") => $publicKey,
            Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN") => $authToken,
            Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING") => $contentEncoding
        ];

        // Insert subscription
        $addSubscription = false;
        $tbl = $this->locator->get(Config("SUBSCRIPTION_TABLE_NAME"));
        $entity = $tbl->EntityClass::createFromArray($row);
        if ($tbl && (!method_exists($tbl, "rowInserting") || $tbl->rowInserting(null, $entity))) {
            $em = $tbl->getEntityManager();
            $em->persist($entity);
            $em->flush();
            if (method_exists($tbl, "rowInserted")) {
                $tbl->rowInserted(null, $entity);
            }
            $addSubscription = true;
        }
        return new JsonResponse(["success" => $addSubscription]);
    }

    /**
     * Send Notifications
     *
     * @param array $subscriptions Array of Subscription
     * @param mixed $payload Payload, see https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/API/notifications/NotificationOptions
     * @return Response
     */
    protected function sendNotifications(array $subscriptions, mixed $payload): Response
    {
        $notifications = array_map(function ($subscription) use ($payload) {
            $options = $payload; // Clone
            return ["subscription" => $subscription, "payload" => $options];
        }, $subscriptions);

        // Send multiple notifications with payload
        foreach ($notifications as $notification) {
            $this->webPush->queueNotification(
                $notification["subscription"],
                json_encode($notification["payload"])
            );
        }

        // Check sent results
        $reports = [];
        foreach ($this->webPush->flush() as $report) { // $this->webPush->flush() returns Generator
            $reports[] = $report->jsonSerialize();
        }
        if (IsDebug()) {
            Log(json_encode($reports));
            $results = $reports;
        } else {
            $results = array_map(fn($report) => ["success" => $report["success"]], $reports); // Return "success" only
        }
        return new JsonResponse($results);
    }

    protected function deleteSubscription(array $subscription): Response
    {
        $user = CurrentUserID() ?? CurrentUserName();
        $endpoint = $subscription["endpoint"] ?? "";
        $publicKey = $subscription["publicKey"] ?? "";
        $authToken = $subscription["authToken"] ?? "";
        $contentEncoding = $subscription["contentEncoding"] ?? "";
        if (
            IsEmpty(Config("SUBSCRIPTION_TABLE_NAME"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_USER"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING"))
        ) {
            return new JsonResponse(["success" => false, "error" => "Invalid subscription table settings"]);
        }
        if (
            IsEmpty($endpoint)
            || IsEmpty($publicKey)
            || IsEmpty($authToken)
            || IsEmpty($contentEncoding)
        ) {
            return new JsonResponse(["success" => false, "error" => "Invalid subscription"]);
        }
        $row = [
            Config("SUBSCRIPTION_FIELD_NAME_USER") => $user,
            Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT") => $endpoint,
            Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY") => $publicKey,
            Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN") => $authToken,
            Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING") => $contentEncoding
        ];
        // Delete subscription
        $deleteSubscription = false;
        $tbl = $this->locator->get(Config("SUBSCRIPTION_TABLE_NAME"));
        $em = $tbl->getEntityManager();
        $entity = $em->getRepository($tbl->EntityClass)->findOneBy([Config("SUBSCRIPTION_PROPERTY_NAME_ENDPOINT") => $endpoint]);
        if ($entity && (!method_exists($tbl, "rowInserting") || $tbl->rowDeleting($entity))) {
            $em->remove($entity);
            $em->flush();
            if (method_exists($tbl, "rowDeleted")) {
                $tbl->rowDeleted($entity);
            }
            $deleteSubscription = true;
        }
        return new JsonResponse(["success" => $deleteSubscription]);
    }
}
