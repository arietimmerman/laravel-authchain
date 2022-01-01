<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Events;

use ArieTimmerman\Laravel\AuthChain\State;
use Illuminate\Queue\SerializesModels;

class Authenticated
{
    use SerializesModels;

    /**
     * @var State
     */
    public $state;

    public function __construct(State $state)
    {
        $this->state = $state;
    }
}
