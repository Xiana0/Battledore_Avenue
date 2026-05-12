<?php

namespace PHPMaker2026\Project1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\VarExporter\VarExporter;

class FieldFactory
{
    protected Request $request;

    public function __construct(
        protected RequestStack $requestStack,
        protected Language $language,
        protected string $projectDir,
    ) {
    }

    public function create(
        BaseDbTable|string $table,
        string $name,
        string $class = DbField::class,
        array $config = []
    ): DbField {
        return new $class($table, $name, $config, $this->language, $this->requestStack->getCurrentRequest());
    }

    public function createAll(BaseDbTable $table): DbFields
    {
        $tableClass = $table->TableVar;
        $cachePath = $this->getCachePath($tableClass);
        $hashPath = $cachePath . '.hash';
        if (!method_exists($table, 'getFieldDefinitions')) {
            throw new \LogicException("Class '$tableClass' must define getFieldDefinitions()");
        }
        $meta = $table->getFieldDefinitions();
        $currentHash = hash('sha256', serialize($meta));
        $cachedHash = is_file($hashPath) ? file_get_contents($hashPath) : null;

        // If no cache or hash mismatch, regenerate
        if (!is_file($cachePath) || $currentHash !== $cachedHash) {
            @mkdir(dirname($cachePath), recursive: true);
            file_put_contents($cachePath, '<?php return ' . VarExporter::export($meta) . ';');
            file_put_contents($hashPath, $currentHash);
        } else {
            $meta = require $cachePath;
        }
        $fieldClass = $table instanceof ReportTable || $table->TableType == "REPORT" ? ReportField::class : DbField::class;
        $fields = new DbFields();
        foreach ($meta as $name => $config) {
            $fields[$name] = $this->create($table, $name, $fieldClass, $config);
        }
        return $fields;
    }

    public function clearCache(string $tableClass): void
    {
        @unlink($this->getCachePath($tableClass));
    }

    protected function getCachePath(string $tableClass): string
    {
        return $this->projectDir . '/var/cache/fields/' . str_replace('\\', '.', $tableClass) . '.php';
    }
}
