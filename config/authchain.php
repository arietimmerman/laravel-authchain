<?php

return [

    'config' => 'ArieTimmerman\Laravel\AuthChain\Config\SimpleConfig',
    
    // The configured moudles
    'modules' => [

    ],

    'chain' => [
        //['from' => 'location', 'to' => 'password2'],
        ['from' => 'password', 'to' => 'sms'],
        ['from' => 'password2', 'to' => 'password'],
        ['from' => 'facebook', 'to' => 'sms']
    ]

];
