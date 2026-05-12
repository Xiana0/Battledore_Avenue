<?php

namespace PHPMaker2026\Project1;

/**
 * Page interface
 */
interface PageInterface
{
    /**
     * Page run
     *
     * @return void
     */
    public function run(): void;

    /**
     * Terminate page
     *
     * @param ?string $url URL for redirection
     * @return void
     */
    public function terminate(?string $url = null): void;
}
