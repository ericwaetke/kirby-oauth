<?php

namespace Thathoff\Oauth\Auth;

use Kirby\Auth\Auth;
use Kirby\Auth\Exception\LoginNotPermittedException;
use Kirby\Auth\Method;
use Kirby\Auth\Status;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Component;
use SensitiveParameter;
use Thathoff\Oauth\Controller;
use Thathoff\Oauth\ProvidersManager;

/**
 * OAuth auth method for the Kirby 6 Panel login view
 *
 * The login itself always happens through a redirect to the
 * OAuth provider (see `Controller::login()`). This method only
 * renders the provider buttons on the login screen.
 */
class OauthMethod extends Method
{
    /**
     * OAuth logins start with a redirect to the provider,
     * so there are no credentials that could be
     * authenticated through the login form.
     *
     * @throws LoginNotPermittedException
     */
    public function authenticate(
        string|null $email,
        #[SensitiveParameter]
        string|null $password = null,
        bool $long = false
    ): User|Status {
        throw new LoginNotPermittedException();
    }

    /**
     * Returns the login form component with all configured provider buttons
     */
    public function form(): Component
    {
        $controller = new Controller();
        $settings   = $controller->settings();
        $providers  = $settings['providers'] ?? [];
        $error      = $controller->oauthError()['msg'] ?? null;

        if (is_array($providers) === false) {
            $providers = [];
        }

        return new Component(
            component:    'k-oauth-login-form',
            providers:    array_values($providers),
            autoRedirect: ($settings['autoRedirect'] ?? false) === true,
            error:        is_string($error) ? $error : null
        );
    }

    public static function icon(): string
    {
        return 'globe';
    }

    /**
     * Enables the method only if at least one
     * OAuth provider is configured
     *
     * @param array<mixed> $options
     */
    public static function isEnabled(Auth $auth, array $options = []): bool
    {
        return (new ProvidersManager($auth->kirby()))->count() > 0;
    }

    /**
     * Adds the oauth method to the `auth.methods` option,
     * unless the site has configured that option explicitly.
     *
     * Registered as a `route:before` hook, so it runs after the
     * options are baked but before any auth method is used.
     * Because it only ever fills an unset default, sites keep
     * full control over the enabled methods and their order
     * via the documented `auth.methods` config option.
     */
    public static function applyDefaultOptions(): void
    {
        $kirby = kirby();

        // respect an explicit `auth.methods` config
        if ($kirby->option('auth.methods') !== null) {
            return;
        }

        // keep the default password login if no providers are configured
        $providers = $kirby->option('thathoff.oauth.providers');

        if (is_array($providers) === false || $providers === []) {
            return;
        }

        // with `onlyOauth`, OAuth is the only enabled method
        // (which also hides the password form);
        // otherwise it is offered as an alternative
        $methods = $kirby->option('thathoff.oauth.onlyOauth', false) === true
            ? ['oauth' => []]
            : ['password' => [], 'oauth' => []];

        $kirby->extend([
            'options' => [
                'auth' => [
                    'methods' => $methods
                ]
            ]
        ]);
    }
}
