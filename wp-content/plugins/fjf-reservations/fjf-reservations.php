<?php
/**
 * Plugin Name: FJF Réservations
 * Description: Système de réservation de créneaux pour FJF Automobiles.
 * Version: 1.0.1
 * Author: Cruz
 * Author URI: /
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constantes utiles du plugin.
define('FJF_RESERVATIONS_VERSION', '1.0.1');
define('FJF_RESERVATIONS_PATH', plugin_dir_path(__FILE__));
define('FJF_RESERVATIONS_URL', plugin_dir_url(__FILE__));

// Chargement des fichiers nécessaires.
require_once FJF_RESERVATIONS_PATH . 'includes/class-fjf-reservations-installer.php';
require_once FJF_RESERVATIONS_PATH . 'includes/class-fjf-reservations-admin.php';
require_once FJF_RESERVATIONS_PATH . 'includes/class-fjf-reservations-frontend.php';

/**
 * Activation du plugin.
 * Crée les tables SQL et initialise les créneaux par défaut.
 */
function fjf_reservations_activate() {
    FJF_Reservations_Installer::install();
}
register_activation_hook(__FILE__, 'fjf_reservations_activate');

// Initialisation du menu admin (barre latérale WordPress).
FJF_Reservations_Admin::init();
FJF_Reservations_Frontend::init();

/**
 * Chargement futur des styles/scripts frontend + admin.
 * On garde la fonction ici pour la suite des étapes.
 */
function fjf_reservations_enqueue_assets() {
    wp_enqueue_style(
        'fjf-reservations-style',
        FJF_RESERVATIONS_URL . 'assets/css/style.css',
        array(),
        FJF_RESERVATIONS_VERSION
    );
}
add_action('wp_enqueue_scripts', 'fjf_reservations_enqueue_assets');
add_action('admin_enqueue_scripts', 'fjf_reservations_enqueue_assets');
