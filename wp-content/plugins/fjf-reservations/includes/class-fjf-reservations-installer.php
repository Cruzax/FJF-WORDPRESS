<?php
/**
 * Installation du plugin FJF Réservations.
 * Ce fichier crée les tables SQL nécessaires.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FJF_Reservations_Installer {

    /**
     * Point d'entrée de l'installation.
     */
    public static function install() {
        self::create_tables();
        self::seed_default_slots();
    }

    /**
     * Crée les tables:
     * - wp_fjf_creneaux
     * - wp_fjf_reservations
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate     = $wpdb->get_charset_collate();
        $table_creneaux      = $wpdb->prefix . 'fjf_creneaux';
        $table_reservations  = $wpdb->prefix . 'fjf_reservations';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_creneaux = "CREATE TABLE {$table_creneaux} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            jour VARCHAR(20) NOT NULL,
            heure VARCHAR(5) NOT NULL,
            statut VARCHAR(20) NOT NULL DEFAULT 'disponible',
            PRIMARY KEY (id),
            UNIQUE KEY unique_slot (jour, heure)
        ) {$charset_collate};";

        $sql_reservations = "CREATE TABLE {$table_reservations} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            prestation VARCHAR(150) NOT NULL,
            jour VARCHAR(20) NOT NULL,
            heure VARCHAR(5) NOT NULL,
            statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_user_id (user_id),
            KEY idx_statut (statut),
            KEY idx_jour_heure (jour, heure)
        ) {$charset_collate};";

        dbDelta($sql_creneaux);
        dbDelta($sql_reservations);
    }

    /**
     * Insère les créneaux standards si la table est vide.
     * Jours: lundi à vendredi
     * Heures: 08:00 à 17:00
     */
    private static function seed_default_slots() {
        global $wpdb;

        $table_creneaux = $wpdb->prefix . 'fjf_creneaux';

        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_creneaux}");
        if ($count > 0) {
            return;
        }

        $jours = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi');

        foreach ($jours as $jour) {
            for ($hour = 8; $hour <= 17; $hour++) {
                $heure = sprintf('%02d:00', $hour);

                $wpdb->insert(
                    $table_creneaux,
                    array(
                        'jour'   => $jour,
                        'heure'  => $heure,
                        'statut' => 'disponible',
                    ),
                    array('%s', '%s', '%s')
                );
            }
        }
    }
}
