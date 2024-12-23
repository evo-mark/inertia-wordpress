<?php

namespace EvoMark\WpVite;

class ViteConfig
{
    public string $uploadsPath = "";
    public string $uploadsUrl = "";
    public string $hotFile;
    public array $dependencies;
    public string $namespace;
    public bool $footer;
    public string $entryHandle;
    public bool $useReact;

    public function __construct(array $configArray)
    {
        $this->uploadsPath = $configArray['uploadsPath'];
        $this->uploadsUrl = $configArray['uploadsUrl'];
        $this->hotFile = $configArray['hotFile'] ?? "hot";
        $this->dependencies = $configArray['dependencies'] ?? [];
        $this->namespace = $configArray['namespace'] ?? "";
        $this->footer = $configArray['footer'] ?? false;
        $this->entryHandle = $configArray['entryHandle'] ?? "";
        $this->useReact = $configArray['useReact'] ?? false;
    }
}
