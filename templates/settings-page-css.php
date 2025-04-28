<?php
/**
 * Admin template: Settings page CSS.
 *
 * @package SureCart WordPress SDK
 * @subpackage Templates
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>
<style>
    .spinner {
        float: none;
    }
    .surecart-container {
        padding:30px;
        background: #fff;
        display: grid;
        gap: 1em;
        max-width: 600px;
    }
    h2 {
        padding: 0;
        margin: 0;
    }
    label {
        display: block;
        font-size: 1.1em;
        margin-bottom: 5px;
    }
    label[hidden] {
        display: none;
    }
    p.submit {
        margin: 5px 0 0;
        padding: 0;
    }
    .button.delete {
        color: #b32d2e;
        border-color:#b32d2e;
    }
    .button.delete:hover {
        color: #b32d2e;
        border-color: transparent;
    }
</style>
