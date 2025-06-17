<?php

namespace App\Http\Middleware;

use App\Traits\BaseApiResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    use BaseApiResponse;
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        abort($this->failed('Unauthorized', 'Unauthorized', 'You are not authorized to access this resource', 401));
    }
}
