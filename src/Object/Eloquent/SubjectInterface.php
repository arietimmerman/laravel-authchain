<?php

namespace ArieTimmerman\Laravel\AuthChain\Object\Eloquent;

use Illuminate\Contracts\Auth\Authenticatable;

interface SubjectInterface
{
    public function toUserInfo();

    public function getIdentifier();

    /**
     * @return Type
     */
    public function getTypeIdentifier();

    public function getApprovedScopes(?string $appId);

    public function getEmail();

    public function getPreferredLanguage();

    public function getMobile();

    public function getUserId();

    public function getUuid();

    public function isActive();
}
