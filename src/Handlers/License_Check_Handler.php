<?php

namespace SureCart\WP\Handlers;

use SC_License;
use SureCart\WP\Services\Registration_Service;
use WP_Error;
use XWP\DI\Decorators\Action;
use XWP\DI\Decorators\Handler;

#[Handler( tag: 'init', priority: 11 )]
class License_Check_Handler {
    /**
     * Constructor
     *
     * @param  Registration_Service $reg Registration service.
     */
    public function __construct( private Registration_Service $reg ) {
    }

    #[Action( tag: 'surecart_%s_license_activate', priority: 10, modifiers: array( 'surecart.id' ) )]
    public function on_license_activate( SC_License $license, bool|\WP_Error $res ): void {
        if ( $res instanceof \WP_Error ) {
            $license->disable_validation();

            return;
        }

        $license->enable_validation();
    }

    #[Action( tag: 'surecart_%s_license_deactivate', priority: 10, modifiers: array( 'surecart.id' ) )]
    public function on_license_deactivate( SC_License $license, bool|\WP_Error $res ): void {
        if ( $res instanceof \WP_Error ) {

            return;
        }

        $license->disable_validation();
    }

    #[Action( tag: 'surecart_%s_license_validation', priority: 10, modifiers: array( 'surecart.id' ) )]
    public function run_license_validation( string $id ): void {
        $license = new SC_License( $id );

        $res = $this->reg->validate( $license );

        if ( ! \is_wp_error( $res ) ) {
            return;
        }

        $license
            ->set_activated( false )
            ->set_status( 'revoked' )
            ->save();

        \xwp_create_notice(
            array(
                'caps'        => array( 'manage_options' ),
                'dismissible' => false,
                'id'          => "surecart_{$id}_license_invalid",
                'message'     => \sprintf(
                    /* translators: %s: Error message. */
                    \esc_html__( 'License validation failed: %s', 'surecart' ),
                    $res->get_error_message(),
                ),
                'persistent'  => true,
                'type'        => 'error',
            ),
        )->save();

        \error_log( \print_r( $res, true ) );
    }
}
