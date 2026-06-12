<?php

namespace KittyBot\Services;

use KittyBot\Storage\ServiceStateRepository;

final class ContainerService
{
    public function __construct(
        private ServiceStateRepository $states,
        private ComposeOverrideWriter $override,
        private DockerClient $docker,
    ) {
        $this->states->seed(ServiceCatalog::all());
    }

    /** @return array<string,bool> */
    public function states(): array
    {
        return $this->states->all();
    }

    public function isEnabled(string $service): bool
    {
        $this->assertKnown($service);
        return $this->states->isEnabled($service);
    }

    public function enable(string $service): bool
    {
        $this->assertOptional($service);
        $this->states->setEnabled($service, true);
        $this->override->write($this->states->all());

        try {
            $this->docker->action($service, 'start');
            return true;
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return false;
            }
            throw $e;
        }
    }

    public function disable(string $service): void
    {
        $this->assertOptional($service);
        $this->states->setEnabled($service, false);
        $this->override->write($this->states->all());

        try {
            $this->docker->action($service, 'stop');
        } catch (\RuntimeException $e) {
            if (!str_contains($e->getMessage(), 'not found')) {
                throw $e;
            }
        }
    }

    public function start(string $service): void
    {
        $this->assertKnown($service);
        if (!$this->states->isEnabled($service)) {
            throw new \RuntimeException("Service '$service' is disabled");
        }
        $this->docker->action($service, 'start');
    }

    public function stop(string $service): void
    {
        $this->assertKnown($service);
        $this->docker->action($service, 'stop');
    }

    public function restart(string $service): void
    {
        $this->assertKnown($service);
        if (!$this->states->isEnabled($service)) {
            throw new \RuntimeException("Service '$service' is disabled");
        }
        $this->docker->action($service, 'restart');
    }

    private function assertKnown(string $service): void
    {
        if (!ServiceCatalog::isKnown($service)) {
            throw new \InvalidArgumentException("Unknown service '$service'");
        }
    }

    private function assertOptional(string $service): void
    {
        if (!ServiceCatalog::isOptional($service)) {
            throw new \InvalidArgumentException("Service '$service' cannot be disabled");
        }
    }
}
