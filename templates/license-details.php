<?php
/**
 * Admin template: License details.
 *
 * @package SureCart WordPress SDK
 * @subpackage Templates
 * @version 1.0.0
 *
 * @var string     $name    Name of the plugin/theme.
 * @var SC_License $license License object.
 */

defined( 'ABSPATH' ) || exit;
?>
<p>
    <?php
    printf(
        // Translators: %s is the name of the plugin/theme.
        esc_html__( 'Thank you for purchasing and activating %s.', 'surecart' ),
        esc_html( $name ),
    );
    ?>

    <br>

    <?php
    $expiry = $license->get_revokes_at()
        // Translators: %s is the expiry date of the license.
        ? sprintf( __( 'until %s.', 'surecart' ), date_i18n( 'F j, Y', $license['revokes_at'] ) )
        : __( 'until the end of time', 'surecart' );

    printf(
        // Translators: %s is the expiry date of the license.
        esc_html__( 'Your license is valid %s', 'surecart' ),
        esc_html( $expiry ),
    );
    ?>
</p>
