<?php

/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain;

use ArieTimmerman\Laravel\AuthChain\Object\Subject;

class PolicyDecisionPoint
{

    /**
     * By default, everything is allowed
     */
    public function isAllowed(?Subject $subject, State $state)
    {
        return true;
    }
}
