<?php

/**
 * Class Container.
 *
 * This will be useful for creation of object.
 * We are using Pimple DI Container, which will be
 * useful for defining services and serves as service
 * locator.
 *
 * @package FireWork\GoogleLogin
 * @since 1.0.0
 */

declare(strict_types=1);

namespace FireWork\GoogleLogin;

use FireWork\GoogleLogin\Interfaces\Container as ContainerInterface;
use Pimple\Container as PimpleContainer;
use InvalidArgumentException;
use FireWork\GoogleLogin\Modules\Assets;
use FireWork\GoogleLogin\Modules\Block;
use FireWork\GoogleLogin\Modules\Login;
use FireWork\GoogleLogin\Modules\OneTapLogin;
use FireWork\GoogleLogin\Modules\Settings;
use FireWork\GoogleLogin\Utils\Authenticator;
use FireWork\GoogleLogin\Utils\GoogleClient;
use FireWork\GoogleLogin\Modules\Shortcode;
use FireWork\GoogleLogin\Utils\TokenVerifier;

/**
 * Class Container
 *
 * @package FireWork\GoogleLogin
 */
class Container implements ContainerInterface
{
    /**
     * Pimple container.
     *
     * @var PimpleContainer
     */
    public $container;

    /**
     * Container constructor.
     *
     * @param PimpleContainer $container Pimple Container.
     */
    public function __construct(PimpleContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Get the service object.
     *
     * @param string $service Service object in need.
     *
     * @return object
     *
     * @throws InvalidArgumentException Exception for invalid service.
     */
    public function get(string $service)
    {
        if (! in_array($service, $this->container->keys(), true)) {
            /* translators: %$s is replaced with requested service name. */
            throw new InvalidArgumentException(sprintf(__('Invalid Service %s Passed to the container', 'login-with-google'), $service));
        }

        return $this->container[ $service ];
    }

    /**
     * Define common services in container.
     *
     * All the module specific services will be defined inside
     * respective module's container.
     *
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function define_services(): void
    {
        /**
         * Define Settings service to add settings page and retrieve setting values.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return Settings
         */
        $this->container['settings'] = function () {
            return new Settings();
        };

        /**
         * Define the login flow service.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return Login
         */
        $this->container['login_flow'] = function (PimpleContainer $c) {
            return new Login($c['gh_client'], $c['authenticator']);
        };

        /**
         * Define a service for Google OAuth client.
         *
         * @param PimpleContainer $c Pimple container instance.
         *
         * @return GoogleClient
         */
        $this->container['gh_client'] = function (PimpleContainer $c) {
            $settings = $c['settings'];

            return new GoogleClient(
                [
                    'client_id'     => $settings->client_id,
                    'client_secret' => $settings->client_secret,
                    'redirect_uri'  => wp_login_url(),
                ]
            );
        };

        /**
         * Define Assets service to add styles or script.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return Assets
         */
        $this->container['assets'] = function () {
            return new Assets();
        };

        /**
         * Define Shortcode service to register shortcode for google login.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return Shortcode
         */
        $this->container['shortcode'] = function (PimpleContainer $c) {
            return new Shortcode($c['gh_client'], $c['assets']);
        };

        /**
         * Define Token Verifier Service.
         *
         * Useful in verifying JWT Auth token.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return TokenVerifier
         */
        $this->container['token_verifier'] = function (PimpleContainer $c) {
            return new TokenVerifier($c['settings']);
        };

        /**
         * One Tap Login Service.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return OneTapLogin
         */
        $this->container['one_tap_login'] = function (PimpleContainer $c) {
            return new OneTapLogin($c['settings'], $c['token_verifier'], $c['gh_client'], $c['authenticator']);
        };

        /**
         * Authenticator utility.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return Authenticator
         */
        $this->container['authenticator'] = function (PimpleContainer $c) {
            return new Authenticator($c['settings']);
        };

        /**
         * Define Block service to add gutenberg block.
         *
         * @param PimpleContainer $c Pimple container object.
         *
         * @return Block
         */
        $this->container['google_login_block'] = function (PimpleContainer $c) {
            return new Block($c['assets'], $c['gh_client']);
        };


        /**
         * Define any additional services.
         *
         * @param ContainerInterface $container Container object.
         *
         * @since 1.0.0
         */
        do_action('FireWorkrkrk.google_login_services', $this);
    }
}
