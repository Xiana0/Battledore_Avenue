<?php

namespace PHPMaker2026\Project1\EventSubscriber;

use PHPMaker2026\Project1\RowInsertFailedEvent;
use PHPMaker2026\Project1\RowUpdateFailedEvent;
use PHPMaker2026\Project1\RowDeleteFailedEvent;
use PHPMaker2026\Project1\AppServiceLocator;
use PHPMaker2026\Project1\Language;
use PHPMaker2026\Project1\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Contracts\EventDispatcher\Event;
use function PHPMaker2026\Project1\Config;
use function PHPMaker2026\Project1\WriteAuditLog;
use Throwable;

/**
 * Audit trail failure subscriber
 */
class AuditTrailFailureSubscriber implements EventSubscriberInterface
{

    public function __construct(
        protected readonly ManagerRegistry $registry,
        protected readonly AppServiceLocator $locator,
        protected readonly Language $language
    ) {}

    public static function getSubscribedEvents(): array
    {
        return Config('AUDIT_TRAIL_TO_DATABASE') && Config('AUDIT_TRAIL_TABLE_NAME')
            ? [
                RowInsertFailedEvent::class => 'writeAuditTrailOnFailure',
                RowUpdateFailedEvent::class => 'writeAuditTrailOnFailure',
                RowDeleteFailedEvent::class => 'writeAuditTrailOnFailure',
            ]
            : [];
    }

    public function writeAuditTrailOnFailure(Event $event): void
    {
        $row = $event->getSubject();
        if (!$row instanceof Entity) {
            return;
        }

        // Get entity manager via ManagerRegistry
        $em = $this->registry->getManagerForClass($row::class);
        if (!$em) {
            return;
        }
        $metadata = $em->getClassMetadata($row::class);
        $tableName = $metadata->getTableName();

        // Get table object from service locator
        if (!$this->locator->has($tableName)) {
            return;
        }
        $table = $this->locator->get($tableName);

        // Ensure audit trail for the action is enabled
        $auditOnAdd = $table->AuditTrailOnAdd ?? false;
        $auditOnEdit = $table->AuditTrailOnEdit ?? false;
        $auditOnDelete = $table->AuditTrailOnDelete ?? false;
        $action = $event->getAction();
        if (
            $action === 'A' && !$auditOnAdd
            || $action === 'U' && !$auditOnEdit
            || $action === 'D' && !$auditOnDelete
        ) {
            return;
        }

        // Get key from entity identifier values
        $key = implode(Config('COMPOSITE_KEY_SEPARATOR'), array_map(
            fn($v) => $v instanceof DateTime || $v instanceof DateTimeImmutable ? $v->format('Y-m-d H:i:s') : $v,
            $row->identifierValues()
        ));

        // Get exception details in compact JSON
        $exceptionJson = $this->formatExceptionToJson($event->getException());

        // Get current user identifier
        $userIdentifier = null;

        // Write audit log
        WriteAuditLog(
            $userIdentifier,
            $action,
            $tableName,
            $this->language->phrase('AuditTrailActionFailed'),
            $key,
            '',
            $exceptionJson
        );
    }

    /**
     * Convert an exception into compact JSON with details and trace
     */
    protected function formatExceptionToJson(?Throwable $exception): string
    {
        if (!$exception) {
            return json_encode(['error' => 'No exception'], JSON_UNESCAPED_SLASHES);
        }
        $flatten = FlattenException::createFromThrowable($exception);
        $data = [
            'class' => $flatten->getClass(),
            'message' => $flatten->getMessage(),
            'file' => $flatten->getFile(),
            'line' => $flatten->getLine(),
            'trace' => $flatten->getTrace(),
        ];
        if ($previous = $flatten->getPrevious()) {
            $data['previous'] = [
                'class' => $previous->getClass(),
                'message' => $previous->getMessage(),
                'file' => $previous->getFile(),
                'line' => $previous->getLine(),
                'trace' => $previous->getTrace(),
            ];
        }
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }
}
