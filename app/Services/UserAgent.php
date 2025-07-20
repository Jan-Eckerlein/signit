<?php

namespace App\Services;

use App\Models\User;
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

    public static function fake(User $fromUser): self
    {
        $request = Request::create('/test', 'GET', [], [], [], [
            'REMOTE_ADDR' => '123.123.123.123',
        ]);

        $request->setUserResolver(function () use ($fromUser) {
            return $fromUser;
        });

        return new self($request);
    }
} 