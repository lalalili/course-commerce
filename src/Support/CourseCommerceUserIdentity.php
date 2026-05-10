<?php

namespace Lalalili\CourseCommerce\Support;

use Illuminate\Contracts\Auth\Authenticatable;

class CourseCommerceUserIdentity implements Authenticatable
{
    public function __construct(private readonly int|string $id) {}

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): int|string
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
