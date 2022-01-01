<?php

namespace ArieTimmerman\Laravel\AuthChain\Repository;

class ConsentRepository
{
    public function getDescriptions($scopes)
    {
        return [
            'scope' => 'description'
        ];
    }
}
