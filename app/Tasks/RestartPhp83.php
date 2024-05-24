<?php

namespace App\Tasks;

class RestartPhp83 extends RestartService
{
    protected string $service = 'php8.3-fpm';
}
