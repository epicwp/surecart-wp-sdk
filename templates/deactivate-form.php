<?php //phpcs:disable Universal.Operators.DisallowShortTernary.Found
/**
 * Admin template: License deactivation form.
 *
 * @package SureCart WordPress SDK
 * @subpackage Templates
 * @version 1.0.0
 *
 * @var SC_License $license License object.
 */

defined( 'ABSPATH' ) || exit;

?>
<input type="hidden" name="sc_license[id]" value="<?php echo esc_attr( $license->get_id() ); ?>">
<input type="hidden" name="sc_license[license_key]"
    id="license_key" value="<?php echo esc_attr( $license->get_license_key() ); ?>">

<p class="submit">
<button name="operation" value="refresh" class="button button-secondary">
        <?php esc_html_e( 'Refresh license', 'surecart' ); ?>
    </button>
    <button name="operation" value="deactivate" class="button delete">
        <?php esc_html_e( 'Deactivate license', 'surecart' ); ?>
    </button>
</p>

<p class="small">
    <?php
    esc_html_e(
        'Deactivating the license will free up an activation slot and allow you to use it on another site.',
        'surecart',
    );
    ?>
    <br>
    <?php
    echo esc_html(
        sprintf(
        // Translators: %1$s: number of activations used, %2$s: activation limit.
            _n(
                'You have used %1$s out of %2$s activation.',
                'You have used %1$s out of %2$s activations.',
                $license->get_usage_limit(),
                'surecart',
            ),
            esc_html( number_format_i18n( $license->get_usage_count() ) ),
            esc_html(
                $license->get_usage_limit()
                    ? number_format_i18n( $license->get_usage_limit() )
                    : '∞',
            ),
        ),
    );
    ?>
</p>
