<?php //phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped, Squiz.Commenting.FunctionComment.IncorrectTypeHint

namespace SureCart\WP;

use DI\Attribute\Inject;
use SC_License;
use SureCart\Client;
use SureCart\WP\Interfaces\Handles_Releases;
use SureCart\WP\Interfaces\Handles_Settings;
use SureCart\WP\Services\Update_Service;
use XWP\DI\Container;

/**
 * License SDK class.
 *
 * @template TUph of Handles_Releases
 *
 * @method string       get_token() Get the SureCart API Public token.
 * @method string       get_id()    Get the ID of this license.
 * @method string       get_type()  Get the type of the license. Can be either 'plugin' or 'theme'.
 * @method string       get_name()  Get the name of the license.
 * @method string       get_slug()  Get the slug of the license.
 *
 * @method class-string<TUph> get_updater() Get the updater class.
 */
class SDK {
    /**
     * SDK Arguments.
     *
     * @var array{
     *   token: string,
     *   type: 'plugin'|'theme',
     *   slug: string,
     *   id: string,
     *   updater: class-string<TUph>,
     *   page: array{
     *     capability: string,
     *     icon_url: string,
     *     location: 'none'|'menu'|'submenu'|'options'|'action',
     *     menu_slug: string,
     *     menu_title: string,
     *     page_title: string,
     *     parent_slug?: string,
     *     position?: int
     *   }
     * }
     */
    private array $config;

    /**
     * SDK constructor.
     *
     * @param  array<string,mixed> $config    SDK configuration.
     * @param  Container|null      $container Container instance.
     */
    public function __construct( array $config, private ?Container $container = null ) {
        $this->config = $config;

        if ( null !== $this->container ) {
            return;
        }

        $this->init_sdk();
    }

    /**
     * Dynamically set the argument.
     *
     * @param  string       $name Method name.
     * @param  array<mixed> $args Arguments.
     * @return mixed
     */
    public function __call( string $name, array $args ): mixed {
        \preg_match( '/^get_(.+)$/', $name, $matches );
        $arg = $matches[1];

        return $this->config[ $arg ] ?? null;
    }

    /**
     * Get the page configuration.
     *
     * @return array{
     *   capability: string,
     *   icon_url: string,
     *   location: 'none'|'menu'|'submenu'|'options'|'action',
     *   menu_slug: string,
     *   menu_title: string,
     *   page_title: string,
     *   parent_slug?: string,
     *   position?: int
     * }
     */
    public function get_page(): array {
        $config = $this->config['page'];

        $config['menu_slug'] = "{$this->config['slug']}-{$config['menu_slug']}";

        return $config;
    }

    /**
     * Get the License object.
     *
     * @return SC_License
     */
    public function get_license(): SC_License {
        return new SC_License( $this->get_id() );
    }

    private function init_sdk(): void {
        // Noop.
    }
}
