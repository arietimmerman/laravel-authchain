<?php

/**
 * A module is a configured type.
 */

namespace ArieTimmerman\Laravel\AuthChain\Module;

use ArieTimmerman\Laravel\AuthChain\AuthChain;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\AuthLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\UserInterface;
use ArieTimmerman\Laravel\AuthChain\Exceptions\AuthFailedException;
use ArieTimmerman\Laravel\AuthChain\Repository\LinkRepositoryInterface;
use ArieTimmerman\Laravel\AuthChain\Repository\UserRepositoryInterface;
use ArieTimmerman\Laravel\AuthChain\Exceptions\ApiException;
use ArieTimmerman\Laravel\AuthChain\Object\Eloquent\SubjectInterface;
use ArieTimmerman\Laravel\AuthChain\PolicyDecisionPoint;
use ArieTimmerman\Laravel\AuthChain\Types\NullType;

class Module extends Model implements ModuleInterface, \JsonSerializable
{

    /**
     * @var ArieTimmerman\Laravel\AuthChain\Types\Type
     */
    public $typeObject;

    /**
     * The levels this module supports
     *
     * @var AuthLevel[]
     */
    public $levels = [];
    public $initialized = false;
    public $identifier = null;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * @return self
     */
    public function init(Request $request, State $state)
    {
        $this->getTypeObject()->init($request, $state, $this);

        $this->enabled = $this->getTypeObject()->isEnabled($state->getSubject());

        $this->initialized = true;

        return $this;
    }

    public function getRememberAttribute($value)
    {
        return $value ?? 'session';
    }

    public function getRememberLifetimeAttribute($value)
    {
        return $value ?? 3600;
    }

    /**
     * Whether the authentication module can complete based on remembered session data
     */
    public function remembered()
    {
        if (!$this->initialized) {
            throw new AuthFailedException('You forgot to initialize this module initialized');
        }

        return $this->getTypeObject()->remembered();
    }

    /**
     * isPassive modules do not prompt for authentication
     */
    public function isPassive()
    {
        return $this->getTypeObject()->isPassive();
    }

    /**
     * @return ModuleResult
     */
    public function process(Request $request, State $state)
    {

        /**
         * @var \ArieTimmerman\Laravel\AuthChain\Module\ModuleResult
         */
        $result = $this->getTypeObject()->process($request, $state, $this);

        if (($subject = $result->getSubject()) != null) {
            $user = $this->findNewMatchingUser($subject, $state->getSubject());

            if ($user == null && $this->getTypeObject()->shouldCreateUser($this)) {
                $user = resolve(UserRepositoryInterface::class)->createForSubject($subject);
                resolve(LinkRepositoryInterface::class)->add($this->getTypeObject(), $subject, $user);
            }

            if ($user != null) {
                $result->getSubject()->setUserId($user->getId());
            }
        }

        // If two modules emitted a subject, we should ensure this relates to the same user
        if ($result->getSubject() != null && $state->getSubject() != null) {

            // TODO: Currently we cannot ensure that two subject emitting modules - not connected to an user - belong to the same entity
            if ($result->getSubject()->getUserId() == null || $state->getSubject()->getUserId() == null) {
                throw new AuthFailedException('One of the subjects does not refer to an user');
            }

            // (1) Er is al een user object bekend => kijk of link niet naar een ander wijst, zo niet match
            if ($result->getSubject()->getUserId() != $state->getSubject()->getUserId()) {
                throw new AuthFailedException('One of the subjects refers to another user');
            }
        }

        if ($result->isCompleted()
            && ($message = resolve(PolicyDecisionPoint::class)->isAllowed($result->getSubject(), $state)) !== true) {
            $result = $this->baseResult()
                ->setResponse(response(null, 403))
                ->addMessage(Message::error($message));
        }

        return $this->after($request, $state, $result);
    }

    /**
     * Find a (stored) user for the retrieved subject. If provided, the fallback subject is assumed to be the user if no other user is found.
     *
     * @return UserInterface
     */
    public function findNewMatchingUser(SubjectInterface $subject, SubjectInterface $fallback = null)
    {
        //TODO: introduce  some kind of correlation logic
        $user = null;

        // If the authentication module somehow already knows the user, no need to do fancy lookups. For example the password module.
        if ($subject->getUserId() != null) {
            $user = resolve(LinkRepositoryInterface::class)->getUserById($subject->getUserId());
        } else {
            $user = resolve(LinkRepositoryInterface::class)->getUser($subject);
        }

        if ($user == null) {
            $user = resolve(UserRepositoryInterface::class)->findForSubject($subject);

            /**
             * TODO: Only do the following if 'blind linking' is allowed.
             */
            if ($user == null && $fallback != null && $fallback->getUserId() != null) {
                $user = resolve(LinkRepositoryInterface::class)->getUser($fallback);
            }

            if ($user != null) {
                resolve(LinkRepositoryInterface::class)->add($this->getTypeObject(), $subject, $user);
            }
        }

        return $user;
    }

    /**
     * Ensures the default authentication levels are set, as well as a reference to the module
     *
     * @return \ArieTimmerman\Laravel\AuthChain\Module\ModuleResult
     */
    public function baseResult()
    {
        return (new ModuleResult())
            ->setLevels($this->getLevels())
            ->setModule($this)->setRememberAlways($this->remember == 'cookie')
            ->setRememberForSession($this->remember == 'session')
            ->setRememberLifetime($this->remember_lifetime);
    }

    /**
     * @return ModuleResult
     */
    protected function after(Request $request, State $state, ModuleResult $moduleResult)
    {
        return $moduleResult;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Get the levels this module supports
     *
     * @return AuthLevel[]
     */
    public function getLevels()
    {
        return $this->levels;
    }

    public function setLevels(array $levels)
    {
        $this->levels = $levels;
    }

    public function syncLevels(array $levels)
    {
        throw new ApiException('Not supported');
    }

    /**
     * Whether this module provides the provided authentication level
     *
     * @return bool
     */
    public function provides($desiredLevel = null, $comparison = 'exact')
    {
        $result = false;

        $desiredLevel = empty($desiredLevel) ? null : $desiredLevel;

        //AuthLevel
        if (is_array($desiredLevel)) {
            $result = false;

            foreach ($desiredLevel as $l) {
                if ($result = $this->provides($l, $comparison)) {
                    break;
                }
            }

            if (count($desiredLevel) == 0) {
                $result = true;
            }
        } else {
            $result = $this->hide_if_not_requested ? false : $desiredLevel == null; // && count($this->levels) > 0

            foreach ($this->getLevels() as $level) {
                $compareResult = $level->compare($desiredLevel);

                if ($comparison == 'exact' && $compareResult == 0) {
                    $result = true;
                    break;
                } elseif ($comparison == 'minimum' && $compareResult >= 0) {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Information for the frontend application
     *
     * WARNING: this is a serialization of the ModuleExecution
     */
    public function jsonSerialize()
    {
        $result = [
            'id' => $this->getIdentifier(),
            'name' => $this->name,
            'type' => $this->getTypeObject() ? $this->getTypeObject()->getIdentifier() : null,

            //TODO: Only returns this to management APIs. Not to public endpoints
            'config' => $this->config,

            'group' => $this->group ?? $this->getTypeObject()->getDefaultGroup(),

            'remember' => $this->remember,
            'remember_lifetime' => $this->remember_lifetime,

            'hide_if_not_requested' => $this->hide_if_not_requested,

            'enabled' => $this->enabled,
            'skippable' => $this->skippable,
            'passive' => $this->isPassive(),
            'levels' => $this->getLevels()
        ];

        return $result;
    }

    /**
     * Used by array_unique function
     */
    public function __toString()
    {
        return $this->getIdentifier();
    }

    public function isHigher(ModuleInterface $module)
    {
        return false;
    }

    /**
     * Get the value of type
     *
     * @return \ArieTimmerman\Laravel\AuthChain\Types\Type
     */
    public function getTypeObject()
    {
        if ($this->typeObject != null) {
            return $this->typeObject;
        }

        // If the type gets renamed, don't get an error
        $this->typeObject = isset(AuthChain::$typeMap[$this->type]) ? new AuthChain::$typeMap[$this->type] : new NullType;

        return $this->typeObject;
    }

    public function getRememberLifetime()
    {
        return null;
    }

    public function getInfo()
    {
        return $this->getTypeObject() ? $this->getTypeObject()->getInfo() : null;
    }


    public static function withTypeAndConfig(Type $type, array $config)
    {
        $m = new self();
        $m->typeObject = $type;

        $m->identifier = $config['id'] ?? get_class($type);

        $m->config = $config;

        $levels = [];
        foreach (($config['levels'] ?? []) as $type => $level) {
            $levels[] = new AuthLevel($type, $level);
        }

        $m->setLevels($levels);

        return $m;
    }
}
