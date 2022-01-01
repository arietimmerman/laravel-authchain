<?php

namespace ArieTimmerman\Laravel\AuthChain\Object\Eloquent;

use Illuminate\Database\Eloquent\Model;
use ArieTimmerman\Laravel\AuthChain\State as RealState;

class State extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'authchain_states';

    protected $casts = [
        // 'state' => 'array',
    ];

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    public function setStateAttribute($value)
    {
        $this->attributes['state'] = json_encode($value);
    }

    public function getStateAttribute($value)
    {
        return RealState::fromCode(request(), $value);
    }
}
