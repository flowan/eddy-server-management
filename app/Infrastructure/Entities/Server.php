<?php

namespace App\Infrastructure\Entities;

class Server
{
    public function __construct(
        public readonly string       $id,
        public readonly Region       $region,
        public readonly ServerSize   $size,
        public readonly Image        $image,
        public readonly ServerStatus $status,
        public array                 $metadata = [],
    ) {
    }
}
