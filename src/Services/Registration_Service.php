<?php

namespace SureCart\WP\Services;

use SC_License;
use SureCart\Client;
use WP_Error;

/**
 * Handles registration and activation of licenses.
 */
class Registration_Service {
    /**
     * Constructor.
     *
     * @param  Client $client Client instance.
     */
    public function __construct( private Client $client ) {
    }

    /**
     * Register the license
     *
     * @param  SC_License $license License object.
     */
    public function register( SC_License $license ): bool|\WP_Error {
        return true;
    }

    /**
     * Activate the license
     *
     * @param  SC_License $license License object.
     * @return true|WP_Error
     */
    public function activate( SC_License $license ): bool|\WP_Error {
        try {
            if ( ! $license->is_registered() ) {
                $this->do_registration( $license );
            }

            if ( ! $license->is_activated() ) {
                $this->do_activation( $license );
            }

            return true;

        } catch ( \Exception $e ) {
            return new \WP_Error(
                'activation_failed',
                \sprintf(
                    /* translators: %s: Error message. */
                    \esc_html__( 'Activation failed: %s', 'surecart' ),
                    $e->getMessage(),
                ),
            );
        }
    }

    public function deactivate( SC_License $license ): bool|\WP_Error {
        try {
            if ( ! $license->is_activated() ) {
                return true;
            }

            $this->do_deactivation( $license );

            return true;

        } catch ( \Exception $e ) {
            return new \WP_Error(
                'deactivation_failed',
                \sprintf(
                    /* translators: %s: Error message. */
                    \esc_html__( 'Deactivation failed: %s', 'surecart' ),
                    $e->getMessage(),
                ),
            );
        }
    }

    public function refresh( SC_License $license ): bool|\WP_Error {
        try {
            $this->do_refresh( $license );

            return true;
        } catch ( \Exception $e ) {
            return new \WP_Error(
                'refresh_failed',
                \sprintf(
                    /* translators: %s: Error message. */
                    \esc_html__( 'Refresh failed: %s', 'surecart' ),
                    $e->getMessage(),
                ),
            );
        }
    }

    public function validate( SC_License $license ): bool|\WP_Error {
        try {
            if ( ! $license->is_registered() ) {
                return new \WP_Error(
                    'license_not_registered',
                    \esc_html__( 'License not registered.', 'surecart' ),
                );
            }

            if ( ! $license->is_activated() ) {
                return new \WP_Error(
                    'license_not_activated',
                    \esc_html__( 'License not activated.', 'surecart' ),
                );
            }

            $this->do_validation( $license );

            return true;

        } catch ( \Exception $e ) {
            return new \WP_Error(
                'validation_failed',
                \sprintf(
                    /* translators: %s: Error message. */
                    \esc_html__( 'Validation failed: %s', 'surecart' ),
                    $e->getMessage(),
                ),
            );
        }
    }

    /**
     * Do the registration
     *
     * @param  SC_License $license License object.
     */
    private function do_registration( SC_License $license ): void {
        $data = $this->client->public()->license()->show( $license->get_license_key() );

        $license->register( $data )->save();
    }

    /**
     * Do the activation
     *
     * @param  SC_License $license License object.
     */
    private function do_activation( SC_License $license ): void {
        $data = $this->client->public()->activation()->create( $license->get_activation() );

        $license->activate( $data )->save();
    }

    private function do_deactivation( SC_License $license ): void {
        $data = $this->client->public()->activation()->delete( $license->get_activation_id() );

        $license->delete();
    }

    private function do_validation( SC_License $license ): void {
        $data = $this->client->public()->activation()->show( $license->get_activation_id() );

        $license->validate( $data )->save();
    }

    private function do_refresh( SC_License $license ): void {
        $data = $this->client->public()->license()->show( $license->get_license_key() );

        unset( $data['status'] );

        $license->register( $data )->save();
    }
}
