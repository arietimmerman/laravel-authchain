<?php

namespace ArieTimmerman\Laravel\AuthChain\Object\Eloquent;

interface UserInterface
{
    public function getId();
    public function getUsername();
    public function getAttributes();
}
