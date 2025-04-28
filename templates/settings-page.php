<?php
/**
 * Admin template: Settings page.
 *
 * @package SureCart WordPress SDK
 * @subpackage Templates
 * @version 1.0.0
 *
 * @var SC_License $license License object.
 */

defined( 'ABSPATH' ) || exit;

$lic_id = $license->get_id();


?>

<div class="wrap">
    <h1></h1>
    <?php settings_errors( 'surecart' ); ?>

    <div class="<?php echo esc_attr( $lic_id ); ?>-form-container surecart-container" >
        <h2><?php echo esc_html( $title ); ?></h2>

        <?php
        /**
         * Fires before the settings form.
         *
         * @param SC_License $license License details.
         * @since 1.0.0
         */
        do_action( "surecart_{$lic_id}_settings_form_before", $license );
        ?>

        <form method="post">

            <?php
            /**
             * Outputs the proper fields for operation.
             *
             * @param 'activate'|'deactivate' $operation Ope
             * @param SC_License $license License details.
             *
             * @since 1.0.0
             */
            do_action( "surecart_{$lic_id}_form_fields", $operation, $license );
            ?>

            <input type="hidden" name="sc_license[id]" value="<?php echo esc_attr( $lic_id ); ?>">
        </form>

    </div>
</div>
