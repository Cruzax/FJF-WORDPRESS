<?php
/**
 * Création de la table wp_fjf_rdv à l'activation du plugin.
 * Utilise dbDelta() pour créer ou mettre à jour la table.
 * dbDelta() est aussi appelé sur plugins_loaded pour ajouter
 * les nouvelles colonnes sur les installations existantes.
 */

if (!defined('ABSPATH')) exit;

function fjf_rdv_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fjf_rdv';
    $table_demandes = $wpdb->prefix . 'fjf_demandes_rdv';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        immatriculation VARCHAR(20) DEFAULT '',
        message TEXT NOT NULL,
        statut VARCHAR(20) DEFAULT 'en_attente',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Table des demandes de creneaux depuis le planning
    $sql_demandes = "CREATE TABLE $table_demandes (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        jour VARCHAR(20) NOT NULL,
        heure VARCHAR(5) NOT NULL,
        statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY jour_heure (jour, heure)
    ) $charset_collate;";

    dbDelta($sql_demandes);
}

// Exécuter à chaque chargement pour ajouter les nouvelles colonnes
// sur une installation déjà existante (sans réactiver le plugin).
add_action('plugins_loaded', 'fjf_rdv_create_table');
