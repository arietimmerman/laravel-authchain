<?php

namespace ArieTimmerman\Laravel\AuthChain;

class StateStorage
{
    public function getEloquentClass()
    {
        return \ArieTimmerman\Laravel\AuthChain\Object\Eloquent\State::class;
    }
    
    public function saveState(State $state)
    {
        return $this->getEloquentClass()::updateOrCreate(
            [
            'id' => $state->getstateId()
            ],
            [
            'state' => $state
            ]
        );
    }

    public function getStateFromSession($stateId)
    {
        $eloquentState = $this->getEloquentClass()::find($stateId);

        return $eloquentState ? $eloquentState->state : null;
    }

    public function deleteState(State $state)
    {
        $this->getEloquentClass()::destroy($state->getstateId());
    }
}
