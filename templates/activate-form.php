<?php
/**
 * Admin template: License activation form.
 *
 * @package SureCart WordPress SDK
 * @subpackage Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>

<p>
<label for="sc_license[license_key]">
    <?php esc_html_e( 'Enter your license key', 'surecart' ); ?>
</label>
<input class="widefat" type="password" autocomplete="off" name="sc_license[license_key]" id="license_key">
<input type="hidden" name="sc_license[id]" value="<?php echo esc_attr( $license->get_id() ); ?>">
<p>
<p class="submit">
    <button name="operation" value="activate" class="button button-primary">
        <?php esc_html_e( 'Activate license', 'surecart' ); ?>
    </button>
</p>
