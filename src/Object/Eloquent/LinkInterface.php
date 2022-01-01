<?php

namespace ArieTimmerman\Laravel\AuthChain\Object\Eloquent;

interface LinkInterface
{

    /**
     * @return UserInterface
     */
    public function getUser();
}
