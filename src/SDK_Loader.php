<?php //phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped, Squiz.Commenting.FunctionComment.IncorrectTypeHint, Squiz.Commenting.FunctionComment.MissingParamName, Squiz.Commenting.FunctionComment.MissingParamTag

namespace SureCart\WP;

use SureCart\WP\Interfaces\Handles_Releases;

/**
 * License SDK class.
 *
 * @template TUh of Handles_Releases
 *
 * @method static with_token(string $token) Set the SureCart API Public token.
 * @method static with_type(string $type)   Set the type of the license. Can be either 'plugin' or 'theme'.
 * @method static with_slug(string $slug)   Set the slug of the plugin or theme.
 * @method static with_id(string $id)       Set the ID of this license. Defaults to plugin or theme slug.
 */
class SDK_Loader {
    /**
     * SDK Arguments.
     *
     * @var array{
     *   id: string,
     *   name: string,
     *   slug: string,
     *   token: string,
     *   type: 'plugin'|'theme',
     *   page: array<string,mixed>,
     *   settings: array<string,mixed>,
     *   updater?: class-string<TUh>,
     * }
     */
    protected array $args;

    /**
     * Get the SDK arguments for the container.
     *
     * @param  array<string,mixed> $args Arguments to set.
     * @return array<string,mixed>
     */
    public static function for_container( array $args ): array {
        return ( new self( $args ) )->get_data();
    }

    /**
     * Create a License instance.
     *
     * @param array{
     *   name?: string,
     *   token?: string,
     *   type?: 'plugin'|'theme',
     *   slug?: string,
     *   id?: string,
     *   updater?: class-string<TUh>,
     * }                     $args Arguments to set.
     */
    public function __construct( array $args ) {
        $this->with_args( $args );
    }

    /**
     * Dynamically set the argument.
     *
     * @param  string       $name Method name.
     * @param  array<mixed> $args Arguments.
     * @return mixed
     */
    public function __call( string $name, array $args ): mixed {
        \preg_match( '/^with_(.+)$/', $name, $matches );
        $arg = $matches[1];

        return $this->with_arg( $arg, ...$args );
    }

    /**
     * Get the SDK arguments.
     *
     * @return array<string,mixed>
     */
    public function get_data(): array {
        return $this->args;
    }

    /**
     * Initialize the SDK.
     *
     * @return object
     */
    public function initialize(): object {
        return $this;
    }

    /**
     * Set the options for the settings page.
     *
     * @param  array{
     *   capability?: string,
     *   icon_url?: string,
     *   location?: 'none'|'menu'|'submenu'|'options'|'action',
     *   action?: string,
     *   menu_slug?: string,
     *   menu_title?: string,
     *   page_title?: string,
     *   parent_slug?: string,
     *   position?: int
     * } $opts Settings page options.
     * @return static
     */
    public function with_page( array $opts ): static {
        return $this->with_arg( 'page', $this->parse_settings( $opts ) );
    }

    /**
     * Set the release update handler.
     *
     * @param  class-string<TUh> $updater Release update handler class.
     * @return static
     */
    public function with_updater( string $updater ): static {
        return $this->with_arg( 'updater', $updater );
    }

    /**
     * Set the configuration arguments.
     *
     * @param  array<string,mixed> $args Arguments to set.
     */
    protected function with_args( array $args ): void {
        $this->set_defaults();

        foreach ( $args as $arg => $val ) {
            $this->{"with_{$arg}"}( $val );
        }
    }

    /**
     * Set the argument.
     *
     * @param  string $name Argument name.
     * @param  mixed  $arg  Argument value.
     * @return static
     */
    protected function with_arg( string $name, mixed $arg ): static {
        $this->args[ $name ] = $arg;

        return $this;
    }

    /**
     * Set the default arguments.
     */
    protected function set_defaults(): void {
        $this->args = array(
            'id'       => '',
            'name'     => '',
            'page'     => $this->parse_settings( array() ),
            'settings' => array(),
            'slug'     => '',
            'token'    => '',
            'type'     => 'plugin',
            'updater'  => Services\Update_Service::class,
        );
    }

    /**
     * Parse the settings options.
     *
     * @param  array<string,mixed> $opts Settings options.
     * @return array<string,mixed>
     */
    protected function parse_settings( array $opts ): array {
        $defaults = array(
            'capability'  => 'manage_options',
            'icon_url'    => '',
            'location'    => 'none',
            'menu_slug'   => 'surecart-license',
            'menu_title'  => 'License',
            'page_title'  => 'Manage License',
            'parent_slug' => null,
            'position'    => null,
        );

        return \xwp_parse_args( $opts, $defaults );
    }
}
