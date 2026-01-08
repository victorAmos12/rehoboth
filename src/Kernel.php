<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        // Set timezone to Africa/Lubumbashi (CAT = UTC+2) for TOTP verification
        // This ensures TOTP codes match between server and user's phone
        date_default_timezone_set('Africa/Lubumbashi');
        
        parent::boot();
    }
}
