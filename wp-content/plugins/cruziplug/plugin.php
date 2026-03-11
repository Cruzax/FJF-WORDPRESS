<?php
/**
 * Plugin Name: Voiture
 * Description: A plugin to integrate.
 * Version: 1.0
 * Author: Cruz
 * Author URI: /
 */

function voiture_admin_menu() {
    add_menu_page(
        'Voiture clients',      // Page title
        'Voiture clients',      // Menu title
        'manage_options',       // Capability
        'voiture-clients',      // Menu slug
        'voiture_clients_page', // Callback
        'dashicons-car',        // Icon
        26                      // Position
    );
}
add_action('admin_menu', 'voiture_admin_menu');

function voiture_enqueue_styles($hook) {
    if ($hook !== 'toplevel_page_voiture-clients') {
        return;
    }
    wp_enqueue_style(
            'cruziplug-admin',
            plugin_dir_url(__FILE__) . 'assets/plugin.css',
            array(),
            time()
        );
}
add_action('admin_enqueue_scripts', 'voiture_enqueue_styles');

function voiture_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'voiture_clients';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        nom VARCHAR(255) NOT NULL,
        prenom VARCHAR(255) NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        km INT(10) UNSIGNED NOT NULL,
        marque_modele VARCHAR(255) NOT NULL,
        immatriculation VARCHAR(20) NOT NULL,
        annee YEAR NOT NULL,
        commentaire TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'voiture_create_table');
add_action('admin_init', 'voiture_create_table');

function voiture_clients_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'voiture_clients';

    // Handle form submission
    if (isset($_POST['voiture_submit']) && check_admin_referer('voiture_add_entry', 'voiture_nonce')) {
        $nom = sanitize_text_field($_POST['nom']);
        $prenom = sanitize_text_field($_POST['prenom']);
        $telephone = sanitize_text_field($_POST['telephone']);
        $km = absint($_POST['km']);
        $marque_modele = sanitize_text_field($_POST['marque_modele']);
        $immatriculation = sanitize_text_field($_POST['immatriculation']);
        $annee = absint($_POST['annee']);
        $commentaire = sanitize_textarea_field($_POST['commentaire']);

        if ($nom && $prenom && $telephone && $km && $marque_modele && $immatriculation && $annee) {
            $wpdb->insert($table_name, array(
                'nom'              => $nom,
                'prenom'           => $prenom,
                'telephone'        => $telephone,
                'km'               => $km,
                'marque_modele'    => $marque_modele,
                'immatriculation'  => $immatriculation,
                'annee'            => $annee,
                'commentaire'      => $commentaire,
            ), array('%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s'));
            echo '<div class="notice notice-success"><p>Véhicule ajouté avec succès.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Veuillez remplir tous les champs.</p></div>';
        }
    }

    // Handle deletion
    if (isset($_GET['delete_id']) && check_admin_referer('voiture_delete_' . $_GET['delete_id'])) {
        $wpdb->delete($table_name, array('id' => absint($_GET['delete_id'])), array('%d'));
        echo '<div class="notice notice-success"><p>Entrée supprimée.</p></div>';
    }

    // Get existing entries
    $entries = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

    // Load the view
    include plugin_dir_path(__FILE__) . 'views/admin-page.php';
}  