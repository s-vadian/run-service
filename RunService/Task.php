<?php

namespace RunService;

class Task {

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $args = [];

    /**
     * @param \RunService\Service $service
     * @param \RunService\Logger $logger
     */
    public function __construct(Service $service, Logger $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    /**
     * Добавление аргумента.
     * @param string $arg
     */
    public function addArg($arg)
    {
        $this->args[] = $arg;
    }

    /**
     * Выполнение задачи.
     */
    public function execute() {
        $tool = $this->service->getTool()->getPath();
        $servicePath = $this->service->getPath();
        $serviceName = $this->service->getName();
        $command = $tool . ' ' . $servicePath . $serviceName . '.php ' . implode(' ', $this->args);
        $this->logger->log($command, Logger::INFO);

        $currentDir = __DIR__;
        
        chdir($servicePath);
        exec($command, $output);
        chdir($currentDir);
    }

    /**
     * @param Service $service
     */
    public function getService($service)
    {
        $this->service = $service;
    }
}