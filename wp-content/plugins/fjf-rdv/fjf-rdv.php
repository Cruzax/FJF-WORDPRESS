<?php
/**
 * Plugin Name: FJF Rendez-vous
 * Description: Formulaire de prise de rendez-vous pour FJF Automobiles.
 * Version: 1.0
 * Author: Cruz
 * Author URI: /
 */

if (!defined('ABSPATH')) exit;

// === Création de la table à l'activation ===
require_once plugin_dir_path(__FILE__) . 'includes/create-table.php';
register_activation_hook(__FILE__, 'fjf_rdv_create_table');

// === Shortcode [fjf_rdv_form] ===
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';

// === Traitement du formulaire ===
require_once plugin_dir_path(__FILE__) . 'includes/handler.php';

// === Reservation de creneau depuis le planning ===
require_once plugin_dir_path(__FILE__) . 'includes/planning-handler.php';

// === Page admin ===
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';

// === Chargement du CSS ===
function fjf_rdv_enqueue_styles() {
    wp_enqueue_style('fjf-rdv-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null);
    wp_enqueue_style('fjf-rdv-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), '1.0');
}
// CSS côté frontend (pages du site)
add_action('wp_enqueue_scripts', 'fjf_rdv_enqueue_styles');

// CSS côté admin (uniquement sur notre page)
function fjf_rdv_enqueue_admin_styles($hook) {
    if ($hook !== 'toplevel_page_fjf-rdv') {
        return;
    }
    wp_enqueue_style('fjf-rdv-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null);
    wp_enqueue_style('fjf-rdv-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(), '1.0');
}
add_action('admin_enqueue_scripts', 'fjf_rdv_enqueue_admin_styles');
