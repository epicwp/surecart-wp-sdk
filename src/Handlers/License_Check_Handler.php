<?php

namespace SureCart\WP\Handlers;

use SC_License;
use SureCart\WP\SDK;
use SureCart\WP\Services\Registration_Service;
use SureCart\WP\Services\Update_Service;
use WP_Error;
use XWP\DI\Decorators\Action;
use XWP\DI\Decorators\Handler;

#[Handler( tag: 'init', priority: 11 )]
class License_Check_Handler {
    /**
     * Constructor
     *
     * @param  SDK<Update_Service>  $sdk SureCart SDK instance.
     * @param  Registration_Service $reg Registration service.
     */
    public function __construct( private SDK $sdk, private Registration_Service $reg ) {
    }

    #[Action( tag: 'surecart_%s_license_migrate', priority: 10, modifiers: array( 'surecart.id' ) )]
    public function do_license_migration( string $id, string $option ): void {
        $license = new SC_License( $id );

        $res = $this->reg->activate( $license );

        if ( ! \is_wp_error( $res ) ) {
            \delete_option( $option );
            return;
        }

        $this->add_notice( $res, $id );

        $license
            ->set_activated( false )
            ->set_status( 'revoked' )
            ->save();
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

    /**
     * Run the license validation.
     */
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

        $this->add_notice( $res, $id );
    }

    /**
     * Add a notice for the license check.
     *
     * @param  \WP_Error $err Error object.
     * @param  string    $id  License ID.
     */
    private function add_notice( \WP_Error $err, string $id ): void {
        \xwp_create_notice(
            array(
                'caps'        => array( 'manage_options' ),
                'dismissible' => false,
                'id'          => "surecart_{$id}_license_invalid",
                'message'     => \sprintf(
                    '<strong>%s</strong>: %s',
                    \esc_html( $this->sdk->get_name() ),
                    \esc_html( $err->get_error_message() ),
                ),
                'persistent'  => true,
                'type'        => 'error',
            ),
        )->save();
    }
}
