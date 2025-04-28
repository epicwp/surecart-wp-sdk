<?php

namespace SureCart\WP;

use Psr\Container\ContainerInterface as Cnt;
use SC_License;
use SureCart\Client;
use SureCart\WP\Interfaces\Handles_Releases;
use XWP\DI\Container;
use XWP\DI\Decorators\Action;
use XWP\DI\Decorators\Module;

#[Module(
    hook: 'init',
    priority: 10,
    handlers: array(
        Handlers\License_Check_Handler::class,
        Handlers\Settings_Page_Handler::class,
    ),
)]
class Surecart_Module {
    /**
     * Configure the module.
     *
     * @return array<string,mixed>
     */
    public static function configure(): array {
        return array(
            'surecart.id'           => \DI\factory( static fn( SDK $sdk ) => $sdk->get_id() ),
            Client::class           => \DI\factory(
                static fn( SDK $sdk ) => new Client( apiKey: $sdk->get_token() ),
            ),
            Handles_Releases::class => \DI\factory(
                static fn( SDK $sdk, Cnt $cnt ) => $cnt->get( $sdk->get_updater() ),
            ),
            SC_License::class       => \DI\autowire()->constructor( data: \DI\get( 'surecart.id' ) ),
            SDK::class              => \DI\autowire()
                ->constructorParameter(
                    'config',
                    \DI\factory( array( SDK_Loader::class, 'for_container' ) )
                        ->parameter( 'args', \DI\get( 'surecart.config' ) ),
                ),
        );
    }

    /**
     * Register the updater.
     *
     * @param  Container             $cnt Container instance.
     * @param  SDK<Handles_Releases> $lic License instance.
     */
    #[Action(
        tag: 'init',
        priority: 11,
        invoke: Action::INV_PROXIED,
        args: 0,
        params: array( Container::class, SDK::class ),
    )]
    public function register_updater( Container $cnt, SDK $lic ): void {
        \xwp_register_updater(
            static fn() => $cnt->get( Handles_Releases::class ),
            'api.surecart.com',
            $lic->get_type(),
        );
    }
}
