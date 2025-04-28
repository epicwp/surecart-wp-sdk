<?php //phpcs:disable Squiz.Commenting.FunctionComment.MissingParamName, Squiz.Commenting.FunctionComment.MissingParamTag
/**
 * SureCart SDK functions.
 *
 * @package    SureCart WP SDK
 * @subpackage Functions
 */

use SureCart\WP\Interfaces\Handles_Releases;
use SureCart\WP\SDK_Loader;

if ( ! function_exists( 'surecart_sdk' ) ) :
    /**
     * Load the SureCart SDK.
     *
     * @template TUp of Handles_Releases
     * @param  array{
     *   token: string,
     *   type: 'plugin'|'theme',
     *   slug: string,
     *   id: string,
     *   updater?: class-string<TUp>,
     * }                     $args Arguments to set.
     * @return SDK_Loader<TUp>
     */
    function surecart_sdk( array $args ): SDK_Loader {
        return new SDK_Loader( $args );
    }
endif;
