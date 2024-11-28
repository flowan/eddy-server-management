<?php

namespace App\Infrastructure\Entities;

enum ServerType: string
{
    case Server = 'server';
    case Database = 'database';
    case Custom = 'custom';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::Server => 'Server',
            self::Database => 'Database Server',
            self::Custom => 'Custom Server',
        };
    }

    public function getDisplayNameWithDescription(): string
    {
        return match ($this) {
            self::Server => 'Server (Caddy, PHP, Redis, Database, Node)',
            self::Database => 'Database Server (MySQL or PostgreSQL)',
            self::Custom => 'Custom Server',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Server => 'heroicon-o-server-stack',
            self::Database => 'heroicon-o-circle-stack',
            self::Custom => 'heroicon-o-cube-transparent',
        };
    }

    public static function toOptions(): array
    {
        return collect(self::cases())->mapWithKeys(function ($item) {
            return [$item->value => $item->getDisplayNameWithDescription()];
        })->toArray();
    }
}
