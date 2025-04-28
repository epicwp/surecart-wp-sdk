<?php //phpcs:disable Universal.Operators.DisallowShortTernary.Found

namespace SureCart\WP\Services;

use SC_License;
use SureCart\WP\Interfaces\Handles_Settings;
use SureCart\WP\SDK;
use WP_Error;

/**
 * Handles settings page for the package.
 */
class Settings_Service implements Handles_Settings {
    /**
     * Page configuration.
     *
     * @var array{
     *   capability: string,
     *   icon_url: string,
     *   location: 'none'|'menu'|'submenu'|'options'|'action',
     *   action?: string,
     *   menu_slug: string,
     *   menu_title: string,
     *   page_title: string,
     *   parent_slug?: string,
     *   position?: int
     * }
     */
    private array $config;

    /**
     * Package ID.
     *
     * @var string
     */
    private string $id;

    /**
     * Package name.
     *
     * @var string
     */
    private string $name;

    /**
     * Hook for the registered settings page.
     *
     * @var false|string
     */
    private bool|string $hook;

    /**
     * Base directory for the SDK.
     *
     * @var string
     */
    private string $basedir;


    /**
     * Notice details.
     *
     * @var array<string,mixed>
     */
    private array $notice = array();

    /**
     * Arguments for the settings page registration.
     *
     * @var array<string,array<string>|string>
     */
    private array $args = array(
        'menu'    => array( 'icon_url' ),
        'options' => array(),
        'shared'  => array( 'page_title', 'menu_title', 'capability', 'menu_slug', 'position' ),
        'submenu' => array( 'parent_slug' ),
    );

    /**
     * Constructor.
     *
     * @param  SDK<Update_Service>  $sdk SDK instance.
     * @param  Registration_Service $reg Registration service instance.
     */
    public function __construct( SDK $sdk, private Registration_Service $reg ) {
        $this->config  = $sdk->get_page();
        $this->name    = $sdk->get_name();
        $this->id      = $sdk->get_id();
        $this->basedir = \dirname( __DIR__, 2 );
    }

    /**
     * Get the package ID.
     *
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Determine if the settings page should be displayed.
     *
     * @return bool
     */
    public function needs_page(): bool {
        return 'none' !== $this->config['location'];
    }

    /**
     * Add the settings page.
     *
     * @return string
     */
    public function add_page(): string {
        [ $cb, $args ] = $this->get_add_page_args();

        return $this->hook ??= $cb( ...$args );
    }

    /**
     * Output the settings page.
     */
    public function output_page(): void {
        if ( $this->notice ) {
            \add_settings_error( ...$this->notice );
        }
        \xwp_get_template( $this->get_template( 'settings-page-css.php' ) );
        \xwp_get_template( $this->get_template( 'settings-page.php' ), $this->get_page_args() );
    }

    /**
     * Output the license details.
     *
     * @param  SC_License $license License object.
     */
    public function output_license_deets( SC_License $license ): void {
        \xwp_get_template(
            $this->get_template( 'license-details.php' ),
            array(
                'license' => $license,
                'name'    => $this->name,
            ),
        );
    }

    /**
     * Output the form for activating/deactivating the license.
     *
     * @param  string     $operation  'activate' or 'deactivate'.
     * @param  SC_License $license    License object.
     */
    public function output_form( string $operation, SC_License $license ): void {
        \xwp_get_template(
            $this->get_template( "{$operation}-form.php" ),
            array( 'license' => $license ),
        );
    }

    /**
     * Process the form submission.
     *
     * @param  SC_License                        $license License object.
     * @param 'activate'|'deactivate'|'refresh' $operation Operation type.
     */
    public function process_form( SC_License $license, string $operation ): void {
        $res = $this->reg->$operation( $license );

        $this->notice = $this->get_notice( $res, $operation );

        /**
         * Fires after the license is activated or deactivated.
         *
         * @param SC_License    $license License object.
         * @param bool|WP_Error $result  Result of the operation.
         *
         * @since 1.0.0
         */
        \do_action( "surecart_{$this->get_id()}_license_{$operation}", $license, $res );
    }

    /**
     * Get the arguments for adding the settings page.
     *
     * @return array{0: string, 1: array<mixed>}
     */
    private function get_add_page_args(): array {
        if ( 'action' === $this->config['location'] ) {
            return array( 'add_action', array( $this->config['action'], array( $this, 'output_page' ), 10, 0 ) );
        }

        $args = \array_merge(
            \xwp_array_slice_assoc( $this->config, ...$this->args['shared'] ),
            \xwp_array_slice_assoc( $this->config, ...$this->args[ $this->config['location'] ] ),
            array( 'callback' => array( $this, 'output_page' ) ),
        );

        $cb = "add_{$this->config['location']}_page";

        return array( $cb, $args );
    }

    /**
     * Get the arguments for the settings page.
     *
     * @return array<string,mixed>
     */
    private function get_page_args(): array {
        $license = new \SC_License( $this->get_id() );

        return array(
            'action'    => "surecart_{$this->get_id()}_license_submit",
            'license'   => $license,
            'name'      => $this->name,
            'operation' => $license->is_activated() ? 'deactivate' : 'activate',
            'title'     => $this->config['page_title'],
        );
    }

    /**
     * Get the template path.
     *
     * @param  string $template Template name.
     * @return string
     */
    private function get_template( string $template ): string {
        return "{$this->basedir}/templates/{$template}";
    }

    /**
     * Get the notice details.
     *
     * @param  bool|\WP_Error          $res Result of the operation.
     * @param  'activate'|'deactivate' $op  Operation type.
     * @return array<string,mixed>
     */
    private function get_notice( bool|\WP_Error $res, string $op ): array {
        return \is_wp_error( $res )
            ? array(
                'code'    => $res->get_error_code(),
                'message' => $res->get_error_message(),
                'setting' => 'surecart',
                'type'    => 'error',
            ) : array(
                'code'    => 'surecart',
                'message' => 'activate' === $op
                    ? \__( 'License activated successfully.', 'surecart' )
                    : \__( 'License deactivated successfully.', 'surecart' ),
                'setting' => 'surecart',
                'type'    => 'success',
            );
    }
}
