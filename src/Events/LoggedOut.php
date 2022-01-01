<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Events;

use ArieTimmerman\Laravel\AuthChain\Module\ModuleResultList;
use ArieTimmerman\Laravel\AuthChain\State;
use Illuminate\Queue\SerializesModels;

class LoggedOut
{
    use SerializesModels;

    /**
     * @var State
     */
    public $state;

    public function __construct()
    {
    }
}
