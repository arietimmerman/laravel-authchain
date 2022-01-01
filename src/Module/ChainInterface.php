<?php

namespace ArieTimmerman\Laravel\AuthChain\Module;

interface ChainInterface
{
    public function getFrom();
    public function getTo();
}
