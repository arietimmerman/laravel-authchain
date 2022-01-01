# Laravel Authentication Chain

This is a general purpose authentication chain that can be use for all authentication methods and federation protocols.

This module is used by [Idaas.nl](https://www.idaas.nl/).

__This module is work in progress__

# Installation

Ensure you exclude authchain requests from CSRF verification.

~~~
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/saml/v2/*',
        '/authchain/*'
    ];
}
~~~

Enable the routes in your `routes/web.php` file.

~~~.php
\ArieTimmerman\Laravel\AuthChain\Providers\RouteProvider::routes();
~~~

The full user details can be retrieved using

~~~.php
ArieTimmerman\Laravel\AuthChain\Object\Eloquent\Subject::find(id);
~~~

Extend Subject.

~~~
<?php

namespace App;

use ArieTimmerman\Passport\HasApiTokens;

class Subject extends \ArieTimmerman\Laravel\AuthChain\Object\Eloquent\Subject {

    use HasApiTokens;

}
~~~


Register new module types with.

~~~.php
AuthChain::addType('\ArieTimmerman\Laravel\AuthChain\Types\Password');
~~~

# Design considerations

## Module

A Module is of a certain Type.

## Subject

A Subject is an authenticated resource owner.
Each time someone logs in, a new subject is created. The Subject MAY be linked to an user via a Link.
The Link holds a user identifier and the static subject identifier.

The reason for this is to allow different subjects to exist as the same time. For example the same Facebook user from the same 

The authenticated subject identifier is based upon the first subject in the chain

Identity has one or more subjects!

During the Chain with have a living subject. Each module may emit a living subject.

After the authentication chain has been completed, the subject is saved. The saved subject is authenticated.

## Authentication response

An AuthResponse can be generated from a State. The State contains all state details. The AuthReponse contains informative information on how to meet the state requirements.

# How it works.

If no authentication level is specified, AuthChain enforces the user to complete all modules he faces.

For example, even if a second-factor is not required by the application, the user MUST still complete it, if he has configured this.

# TODO

TODO: Remove dependy to \App\User and possible other optional laravel classes