<?php
/**
 * An authentication chain is a connected set of authentication modules, structured in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\Link;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\UserInterface;
use App\User;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\SubjectInterface;

class LinkRepository implements LinkRepositoryInterface
{
    public function getLinkClass()
    {
        return Link::class;
    }

    public function getUserById($userId)
    {
        return User::find($userId);
    }

    public function getUser(SubjectInterface $subject, $moduleExact = false)
    {
        if ($subject->getUserId() != null) {

            //TODO: Load the user directly. Problem is that the user class is unknown here
            $link = ($this->getLinkClass())::where(['user_id', $subject->getUserId()])->first();

            return $link ? $link->getUser() : null;
        } else {
            $link = ($this->getLinkClass())::where(
                [
                'subject_type' => $subject->getTypeIdentifier(),
                //'subject_module' => $module ? $module->getIdentifier() : null,
                'subject_id' => $subject->getIdentifier()
                ]
            )->first();
            
            return $link ? $link->getUser() : null;
        }
    }

    public function add(Type $type, SubjectInterface $subject, UserInterface $user, ?ModuleInterface $module = null)
    {
        $link = ($this->getLinkClass())::create(
            [

            'user_id' => $user->getId(),
            'subject_type' => $type->getIdentifier(),
            'subject_module' => $module ? $module->getIdentifier() : null,
            'subject_id' => $subject->getIdentifier()

            ]
        );
        
        return $link;
    }
}
