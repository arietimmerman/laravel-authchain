<?php

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleResultList;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\SubjectInterface;
use ArieTimmerman\Laravel\AuthChain\State;

interface SubjectRepositoryInterface
{

    /**
     * Used to retrieve the subject from the database. Used for user sessions.
     *
     * @return SubjectInterface
     */
    public function get($id);

    /**
     * It's advisable to delete subjects after a period of time
     */
    public function save(SubjectInterface $subject, State $state);

    public function with(?string $identifier, Type $type, ?ModuleInterface $module = null);

    public function fromModuleResults(?ModuleResultList $moduleResultList);
}
