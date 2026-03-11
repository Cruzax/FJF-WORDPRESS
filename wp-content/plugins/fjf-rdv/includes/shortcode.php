<?php
/**
 * Shortcode [fjf_rdv_form]
 * Affiche le formulaire de prise de rendez-vous sur le frontend.
 */

if (!defined('ABSPATH')) exit;

function fjf_rdv_form_shortcode() {
    // On capture le HTML au lieu de l'afficher directement
    ob_start();
    include plugin_dir_path(__DIR__) . 'views/form.php';
    return ob_get_clean();
}
add_shortcode('fjf_rdv_form', 'fjf_rdv_form_shortcode');
