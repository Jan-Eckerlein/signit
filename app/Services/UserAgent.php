<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Faker\Factory as Faker;

class UserAgent
{

    public function __construct(
        public string $ip,
        public ?string $email,
        public ?string $name
    ) { }

    public static function fromRequest(Request $request): self
    {
        return new self($request->ip(), $request->user()?->email, $request->user()?->name);
    }

    public static function fake(User $fromUser): self
    {
        $faker = Faker::create();
        $ip = $faker->ipv4();

        return new self($ip, $fromUser->email, $fromUser->name);
    }
} 