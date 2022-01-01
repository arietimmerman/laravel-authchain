<?php
/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\SubjectInterface;

interface UserRepositoryInterface
{
    public function createForSubject(SubjectInterface $subject);

    public function findForSubject(SubjectInterface $subject);

    public function findByIdentifier(?string $identifier);
}
