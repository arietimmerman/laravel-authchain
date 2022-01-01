<?php
/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Config\Config;
use ArieTimmerman\Laravel\AuthChain\Module\Module;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\Module\Chain;
use ArieTimmerman\Laravel\AuthChain\Module\ChainInterface;
use ArieTimmerman\Laravel\AuthChain\Exceptions\ApiException;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\Link;
use ArieTimmerman\Laravel\AuthChain\Object\UserDummy;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\SubjectInterface;

class UserRepository implements UserRepositoryInterface
{
    public function createForSubject(SubjectInterface $subject)
    {
        $user = new UserDummy();
        
        $user->setUsername($subject->getIdentifier());

        return $user;
    }

    /**
     * Should get implemented by an implementer
     */
    public function findForSubject(SubjectInterface $subject)
    {
        $user = new UserDummy();
        $user->setId($subject->getIdentifier());

        return $user;
    }

    public function findByIdentifier(?string $identifier)
    {
        return null;
    }
}
