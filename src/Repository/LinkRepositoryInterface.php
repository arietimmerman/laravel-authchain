<?php

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Module\ChainInterface;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\UserInterface;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\SubjectInterface;

interface LinkRepositoryInterface
{
    public function getLinkClass();
    
    public function getUser(SubjectInterface $subject, $moduleExact = false);

    public function getUserById($userId);

    public function add(Type $type, SubjectInterface $subject, UserInterface $user, ?ModuleInterface $module = null);
}
