<?php

namespace ArieTimmerman\Laravel\AuthChain;

use ArieTimmerman\Laravel\AuthChain\Exceptions\AuthFailedException;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleList;
use ArieTimmerman\Laravel\AuthChain\Module\Module;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use ArieTimmerman\Laravel\AuthChain\Exceptions\DidPromptException;
use ArieTimmerman\Laravel\AuthChain\Exceptions\PassiveImpossibleException;
use ArieTimmerman\Laravel\AuthChain\Session;
use ArieTimmerman\Laravel\AuthChain\Http\CompleteProcessorInterface;
use ArieTimmerman\Laravel\AuthChain\Repository\SubjectRepositoryInterface;

class Helper
{
    public static function getModulesForState(State $state)
    {
        return self::getModulesThatLeadsTo(
            $state,
            $state->getRequiredAuthLevel(),
            'exact',
            $state->getLastCompletedModule(),
            $state->isPassive(),
            $state->needsPrompt()
        );
    }

    /**
     *
     * @param  AuthLevel[] $desiredAuthLevel
     * @param  string      $comparison
     * @param  Module      $start            The starting point
     * @return ModuleList
     */
    public static function getModulesThatLeadsTo(State $state, array $desiredAuthLevel = null, $comparison = 'exact', ModuleInterface $start = null, $passive = false)
    {
        if ($comparison != 'exact') {
            throw new AuthFailedException('Only exact is supported for authentication level comparison');
        }

        $destinations = [];

        /* @var $authChain ArieTimmerman\Laravel\AuthChain\AuthChain */
        $authChain = resolve(AuthChain::class);

        $destinationCandidates = $authChain->getAllSuccessorsOf($start);

        Log::debug(sprintf('%s has %d successors', $start == null ? 'null' : $start->name, count($destinationCandidates)));

        foreach ($destinationCandidates as $module) {
            if ($module->enabled && $module->provides($desiredAuthLevel, $comparison)) {
                if ($passive) {
                    if ($module->remembered() || $module->isPassive()) {
                        Log::debug(sprintf('%s (%s) is a possibile destination for level %s', $module->name, $module->getIdentifier(), json_encode($desiredAuthLevel)));
                        $destinations[] = $module;
                    } else {
                        Log::debug(sprintf('%s (%s) cannot be used passivly', $module->name, $module->getIdentifier()));
                    }
                } else {
                    Log::debug(sprintf('%s (%s) is a possibile destination for level %s', $module->name, $module->getIdentifier(), json_encode($desiredAuthLevel)));
                    $destinations[] = $module;
                }
            } else {
                if (!$module->enabled) {
                    Log::debug(sprintf('%s (%s) is not enabled', $module->name, $module->getIdentifier()));
                } else {
                    Log::debug(sprintf('%s (%s) does not provided the required authentication level: %s', $module->name, $module->getIdentifier(), json_encode($desiredAuthLevel)));
                }
            }
        }

        if ($start != null) {
            Log::debug(' ------ START -------');
        }

        Log::debug(sprintf('%s has %d possible destinations: %s', $start == null ? 'null' : $start->name, count($destinations), json_encode($destinations)));

        $nextSteps = [];

        foreach ($destinations as $destination) {
            Log::debug(sprintf('check next steps to %s (%s)', $destination->name, $destination->getIdentifier()));
            foreach ($authChain->getNextSteps($start, $destination, $passive) as $n) {

                //TODO: Do NOT add if $n exists in
                $skip = false;
                foreach ($state->getModuleResults()->toArray() as $result) {
                    if ($result->getModule() == $n) {
                        $skip = true;
                        break;
                    }
                }

                if ($skip) {
                    continue;
                }

                Log::debug(sprintf('%s (%s) is next for destination: %s', $n->name, $n->getIdentifier(), $destination->name));

                $nextSteps[] = $n;
            }
        }

        Log::debug(sprintf('%s has %d possible next steps', $start == null ? 'null' : $start->name, count($nextSteps)));

        $nextSteps = new ModuleList(array_unique($nextSteps));

        if (
            $state->getLastCompleted() != null
            && $state->getSubject() != null
            && $state->getLastCompletedModule()->getIdentifier() != 'consent'

            && (($state->getRequiredAuthLevel() != null && $state->provides($state->getRequiredAuthLevel(), 'exact') && $nextSteps->maySkipAll())

                || ($state->getRequiredAuthLevel() == null && $nextSteps->isEmpty()))
        ) {
            Log::debug(sprintf('%s has consent module as next step', $start == null ? 'null' : $start->name));
            $consent = resolve(AuthChain::class)->getConsentModule();
            $consent->init(request(), $state);

            //TODO: Now all next steps are cleaned. In theory, one could choose to already use extra strong authentication mechanisms in case the user expects he needs these
            // $nextSteps = [];
            $nextSteps[] = resolve(AuthChain::class)->getConsentModule();
        }

        return $nextSteps;
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public static function getCompleteResponse(Request $request, State $state)
    {

        // if there is no subject, redirect to the cancel url
        if ($state->getSubject() == null) {
            return resolve(CompleteProcessorInterface::class)->onCancel($request, $state);
        }

        $eloquentSubject = resolve(SubjectRepositoryInterface::class)->save($state->getSubject(), $state);

        $state->getSubject()->setUuid($eloquentSubject->id);

        Session::login($eloquentSubject, $state);

        return resolve(CompleteProcessorInterface::class)->onFinish($request, $state, $eloquentSubject);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public static function getAuthResponseAsRedirect(Request $request, State $state, $uriIndex = 0, $init = false)
    {
        $response = null;

        // Must be called before everything else
        $authResponse = self::getAuthResponseObject($request, $state);
        $next = $authResponse->getNext();

        // If autocompletes while in a popup window (Facebook/Google popup), do not autocomplete.
        if ($state->done && ($state->display != 'popup' || $state->getLastCompletedModule()->isPassive())) {
            // Should always be loaded within iframe itself??
            // TODO: if popup, simply postMessage with refresh_state. No need to get complete response here.
            // For OIDC, it returns a 302 with code etc, or a web_message for popups.
            // For SAML, it returns a 302 or a 200 with a form.
            $response = Helper::getCompleteResponse($request, $state);
        } elseif ( // DO NOT autoredirect for popup. Because for example window.open should be used for Facebook
            $state->display != 'popup'
            // Check if it is possible to "auto redirect" to a module. Such as Facebook login. Or OIDC login
            && $next != null
            && $next->count() == 1
            && $next[0]->getTypeObject()->canAutoRedirect()
            && $state->getIncomplete() == null
        ) {
            // transform redirect to postMessage oid
            $module = $next[0];
            $result = $module->getTypeObject()->getRedirectResponse($request, $state, $module);
            $state->addResult($result);

            self::saveState($state);

            // This should either use 302 or webmessage for iframes
            $response = $result->getResponse();
        } else {
            $redirectUris = $state->uiServer->getRedirectionUrls();

            self::saveState($state);

            $url = $redirectUris[$uriIndex] . '#' . http_build_query(['state' => (string) $state]);

            $response = response()->view('authchain::redirect', ['url' => $url], 302)->withHeaders(
                [
                    'Expires' => 0,
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Location' => $url
                ]
            );
        }

        if ($init || $state->display != 'popup' || $response->getStatusCode() != 302) {
            return $response;
        } else {

            // Do not send 302 for popup (=iframe) display. We want to redirect the parent! Not the iframe window.
            // Therefore, use postMessage to top window.
            $response->setStatusCode(200);
            $location = $response->headers->get('location');
            $response->headers->remove('location');

            return $response->setContent(
                view(
                    'authchain::redirect-parent',
                    [
                        'settings' => [
                            'location' => $location,
                            'target' => '*',
                        ]
                    ]
                )->render()
            );
        }
    }

    /**
     * @return Response
     */
    public static function getAuthResponse(Request $request, State $state)
    {
        $authResponse = self::getAuthResponseObject($request, $state);
        $state = $authResponse->getState();

        $response = response('');

        if ($authResponse->getIncomplete() != null && $authResponse->getIncomplete()->getResponse() != null) {
            $response = $authResponse->getIncomplete()->getResponse();
        }

        // Save the session state!
        self::saveState($state);

        return $response
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-AuthRequest', (string) $authResponse);
    }

    public static function saveState(State $state)
    {
        return resolve(StateStorage::class)->saveState($state);
    }


    public static function loadStateFromSession($app, $stateId)
    {
        $state = self::getStateFromSession($stateId);
        $app->instance('ArieTimmerman\Laravel\AuthChain\State', $state);
        return $state;
    }

    /**
     * @return \ArieTimmerman\Laravel\AuthChain\State
     */
    public static function getStateFromSession($stateId)
    {
        return resolve(StateStorage::class)->getStateFromSession($stateId);
    }

    public static function deleteState(State $state)
    {
        resolve(StateStorage::class)->deleteState($state);
    }

    /**
     * Not a pure getter, Procceses modules
     *
     * @return \ArieTimmerman\Laravel\AuthChain\AuthResponse
     */
    public static function getAuthResponseObject(Request $request, State $state)
    {
        $result = null;

        $successors = self::getModulesThatLeadsTo($state, $state->getRequiredAuthLevel(), 'exact', $state->getLastCompletedModule(), $state->isPassive());

        Log::debug('May skip all?: ' . ($successors->maySkipAll() ? 'true' : 'false'));

        // If there is something not completed, return with that information
        if ($state->getIncomplete() != null) {
            Log::debug('has incomplete');

            $result = (new AuthResponse())->setIncomplete($state->getIncomplete())->setState($state)->setNext($successors);

            // If the last completed moduleresult provides the required authentication level, AND all other modules may be skipped, continue
        } elseif ($state->getLastCompleted() != null && $state->getSubject() != null && $state->provides($state->getRequiredAuthLevel(), 'exact') && $successors->maySkipAll()) {
            Log::debug('check if last is completed');

            if ($state->needsPrompt() && !$state->getModuleResults()->hasPrompted()) {
                throw new DidPromptException('Not prompted while this was required.');
            }

            $result = (new AuthResponse())->setState($state->setDone(true));
        } elseif ($successors->isEmpty()) {
            if ($state->getLastCompleted() == null) {
                Log::error('Ran out of options because not a single module was authenticated');
            }

            if ($state->getSubject() == null) {
                Log::error('Ran out of options because not a subject was discovered');
            }

            if (!$state->provides($state->getRequiredAuthLevel(), 'exact')) {
                Log::error(sprintf('Ran out of options because the required authentication level (%s) was not obtained. Obtained %s', json_encode($state->getRequiredAuthLevel()), json_encode($state->getLevels())));
            }

            if (!$successors->maySkipAll()) {
                Log::error('Ran out of options because some successors may not be skipped');
            }

            // $last = $state->getLastCompleted()
            Log::debug('Got: ' . json_encode($state->getModuleResults()) . ' but expected: ' . json_encode($state->getRequiredAuthLevel()));

            throw new AuthFailedException('We ran out of options while we have not obtained our required authentication level! Cannot authenticate.');
        } else {
            Log::debug('the else case');

            /**
             * Get all modules that support auto completion. Returns no modules if prompt is required and this is the last step.
             */
            $autoComplete = self::getAutoCompletionModules($successors, $state);
            /**
             * Loop over all modules that support auto completion. Try to complete one.
             */
            foreach ($autoComplete->getModules() as $s) {

                /* @var ModuleInterface $s */
                $moduleResult = $s->process($request, $state);

                if ($moduleResult->isCompleted()) {
                    $state->addResult($moduleResult);

                    $result = self::getAuthResponseObject($request, $state);
                    break;
                }
            }

            if ($result == null) {
                if ($state->isPassive()) {
                    throw new PassiveImpossibleException('Not possible to do passive authentication!');
                }

                $result = (new AuthResponse())
                    ->setNext($successors)
                    ->setState($state);
            }
        }

        return $result;
    }

    /**
     * Returns a list of all modules that allow auto completion
     *
     * @return ModuleList
     */
    private static function getAutoCompletionModules(ModuleList $modules, State $state)
    {
        $autoComplete = new ModuleList();

        foreach ($modules->getModules() as $module) {

            /**
             * @var ModuleInterface $module  
             */
            if ($module->getTypeObject()->getIdentifier() != 'start'
                && $module->provides($state->getRequiredAuthLevel(), 'exact')
                && $state->needsPrompt()
                && !$state->getModuleResults()->hasPrompted()
            ) {
                // Ensure the user is prompted for re-authentication
                $autoComplete = new ModuleList();
                break;
            } elseif ($module->remembered() || $module->isPassive()) {
                $autoComplete[] = $module;
            }
        }

        $autoComplete->sort();

        return $autoComplete;
    }
}
