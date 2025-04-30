<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * Update_Service class file.
 *
 * @package    SureCart WP SDK
 * @subpackage Services
 */

namespace SureCart\WP\Services;

use SC_License;
use SureCart\Client;
use SureCart\WP\Interfaces\Handles_Releases;
use SureCart\WP\SDK;

/**
 * Handle updates for the package.
 */
class Update_Service implements Handles_Releases {
    /**
     * License object.
     *
     * @var SC_License
     */
    private SC_License $lic;

    /**
     * Package slug.
     *
     * @var string
     */
    private string $slug;

    /**
     * Constructor
     *
     * @param  SDK<Update_Service> $sdk SDK instance.
     * @param  Client              $cln Client instance.
     */
    public function __construct( SDK $sdk, private Client $cln ) {
        $this->lic  = $sdk->get_license();
        $this->slug = $sdk->get_slug();
    }

    /**
     * Check if the package can be updated.
     *
     * @param  string $package_file Package file path.
     * @return bool
     */
    public function can_update( string $package_file ): bool {
        return \str_starts_with( $package_file, $this->slug );
    }

    /**
     * Get the update data for the package.
     *
     * @param  string        $package_file Package file path.
     * @param  array<string> $locales      Array of locales.
     * @return array<string,mixed>|false
     */
    public function get_update_data( string $package_file, array $locales ): array|bool {
        if ( ! $this->lic->is_activated() || ! $this->can_update( $package_file ) ) {
            return false;
        }

        $release = $this->cln->public()->license()->expose(
            $this->lic->get_license_key(),
            $this->lic->get_activation_id(),
            3 * \HOUR_IN_SECONDS,
        );

        if ( ! isset( $release['release_json'] ) ) {
            return false;
        }

        return \array_merge(
            $release['release_json'],
            array(
                'download_link' => $release['url'],
                'package'       => $release['url'],
            ),
        );
    }
}
