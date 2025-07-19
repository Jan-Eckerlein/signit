<?php

namespace App\Services;

use Illuminate\Http\Request;

class UserAgent
{
    public string $ip;
    public ?string $email;
    public ?string $name;

    public function __construct(Request $request)
    {
        $this->ip = $request->ip();
        $this->email = $request->user()?->email;
        $this->name = $request->user()?->name;
    }
} 