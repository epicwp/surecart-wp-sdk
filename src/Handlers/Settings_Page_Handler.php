<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing

namespace SureCart\WP\Handlers;

use SC_License;
use SureCart\WP\Mixins\Hook_Methods;
use SureCart\WP\SDK;
use SureCart\WP\Services\Settings_Service;
use XWP\DI\Decorators\Action;
use XWP\DI\Decorators\Handler;

#[Handler( tag: 'init', priority: 11, context: Handler::CTX_ADMIN )]
class Settings_Page_Handler {
    use Hook_Methods;

    /**
     * Constructor.
     *
     * @param  Settings_Service $svc Settings service.
     */
    public function __construct( private Settings_Service $svc ) {
    }

    /**
     * Add the settings page
     */
    #[Action( tag: 'admin_menu', priority: 10 )]
    public function add_page(): void {
        if ( ! $this->svc->needs_page() ) {
            return;
        }

        $this->svc->add_page();
    }

    /**
     * Output the license details on the settings page.
     *
     * @param  SC_License $license License object.
     */
    #[Action( tag: 'surecart_%s_settings_form_before', priority: 10, modifiers: array( 'surecart.id' ) )]
    public function output_license_details( SC_License $license ): void {
        if ( ! $license->is_activated() ) {
            return;
        }

        $this->svc->output_license_deets( $license );
    }

    /**
     * Output the form fields on the settings page.
     *
     * @param  'activate'|'deactivate' $operation Operation type.
     * @param  SC_License              $license   License object.
     */
    #[Action( tag: 'surecart_%s_form_fields', priority: 10, modifiers: array( 'surecart.id' ) )]
    public function output_form_fields( string $operation, SC_License $license ): void {
        $this->svc->output_form( $operation, $license );
    }

    #[Action( tag: 'init', priority: 12, )]
    public function handle_form_submission(): void {
        $data = \xwp_fetch_post_var( 'sc_license', array() );
        $op   = \xwp_fetch_post_var( 'operation', '' );

        // @phpstan-ignore booleanOr.alwaysTrue
        if ( array() === $data || '' === $op ) {
            return;
        }

        // @phpstan-ignore deadCode.unreachable
        $this->svc->process_form( SC_License::from_data( $data ), $op );
    }

    protected function get_hooks(): array {
        return array(
            'admin_menu' => array(
                'args'     => 0,
                'methods'  => array( 'add_page' ),
                'priority' => 10,
                'type'     => 'action',
            ),
        );
    }
}
