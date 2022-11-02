<?php

namespace TruongBo\ProxyRotation\ProxyServer;

interface ProxyClusterInterface
{
    public function isEmpty(): bool;

    public function getNode(int $index): ProxyNode|null;

    public function count(): int;

    public function sort(string $type = "ASC"): self;
}
