<?php

declare(strict_types=1);

namespace App\Interface\Request;

interface RefreshTokenRequestInterface {
    public function getRefreshToken();
}