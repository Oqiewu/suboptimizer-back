<?php

declare(strict_types=1);

namespace App\Request\Token;

interface RefreshTokenRequestInterface {
    public function getRefreshToken();
}