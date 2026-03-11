<?php
/**
 * Page admin : ajoute un menu "FJF Rendez-vous" dans l'admin WordPress.
 */

if (!defined('ABSPATH')) exit;

function fjf_rdv_admin_menu() {
    add_menu_page(
        'FJF Rendez-vous',       // Titre de la page
        'FJF Rendez-vous',       // Titre du menu
        'manage_options',        // Capacité requise
        'fjf-rdv',               // Slug du menu
        'fjf_rdv_admin_page',    // Fonction callback
        'dashicons-calendar-alt', // Icône
        27                       // Position
    );
}
add_action('admin_menu', 'fjf_rdv_admin_menu');

function fjf_rdv_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'fjf_rdv';

    // -------------------------------------------------------
    // Mise à jour du statut
    // Déclenché par ?set_status=...&id=... (+ nonce de sécurité)
    // -------------------------------------------------------
    if (isset($_GET['set_status'], $_GET['id']) && check_admin_referer('fjf_rdv_status_' . $_GET['id'])) {

        // On n'accepte que les trois valeurs autorisées
        $statuts_autorises = array('en_attente', 'valide', 'refuse');
        $nouveau_statut    = sanitize_text_field($_GET['set_status']);

        if (in_array($nouveau_statut, $statuts_autorises, true)) {
            $wpdb->update(
                $table_name,
                array('statut' => $nouveau_statut),    // colonne à modifier
                array('id'     => absint($_GET['id'])), // condition WHERE id = ?
                array('%s'),  // format de la nouvelle valeur
                array('%d')   // format de la condition
            );
        }

        // Redirection pour avoir une URL propre (évite le double-envoi au refresh)
        wp_safe_redirect(admin_url('admin.php?page=fjf-rdv&updated=statut'));
        exit;
    }

    // -------------------------------------------------------
    // Suppression d'une demande
    // Déclenché par ?delete_id=... (+ nonce de sécurité)
    // -------------------------------------------------------
    if (isset($_GET['delete_id']) && check_admin_referer('fjf_rdv_delete_' . $_GET['delete_id'])) {
        $wpdb->delete($table_name, array('id' => absint($_GET['delete_id'])), array('%d'));

        wp_safe_redirect(admin_url('admin.php?page=fjf-rdv&updated=supprime'));
        exit;
    }

    // -------------------------------------------------------
    // Affichage d'un message de confirmation après redirection
    // -------------------------------------------------------
    if (isset($_GET['updated'])) {
        if ($_GET['updated'] === 'statut') {
            echo '<div class="notice notice-success is-dismissible"><p>Statut mis à jour.</p></div>';
        } elseif ($_GET['updated'] === 'supprime') {
            echo '<div class="notice notice-success is-dismissible"><p>Demande supprimée.</p></div>';
        }
    }

    // -------------------------------------------------------
    // 3 requêtes SQL séparées — une seule table, filtrée par statut
    // -------------------------------------------------------

    // Demandes en attente (inclut aussi les lignes sans statut défini)
    $en_attente = $wpdb->get_results(
        "SELECT * FROM {$table_name}
         WHERE statut = 'en_attente' OR statut IS NULL OR statut = ''
         ORDER BY created_at DESC"
    );

    // Demandes validées
    $valides = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE statut = %s ORDER BY created_at DESC",
            'valide'
        )
    );

    // Demandes refusées
    $refuses = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE statut = %s ORDER BY created_at DESC",
            'refuse'
        )
    );

    // Charger la vue
    include plugin_dir_path(__DIR__) . 'views/admin.php';
}
