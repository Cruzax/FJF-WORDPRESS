<?php
/**
 * Logique frontend du plugin FJF Réservations.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FJF_Reservations_Frontend {

    /**
     * Initialise les hooks frontend.
     */
    public static function init() {
        add_action('admin_post_fjf_reservations_book_slot', array(__CLASS__, 'handle_book_slot'));
        add_action('admin_post_nopriv_fjf_reservations_book_slot', array(__CLASS__, 'handle_book_slot'));
    }

    /**
     * Traite la réservation d'un créneau depuis la page planning.
     */
    public static function handle_book_slot() {
        if (!is_user_logged_in()) {
            self::redirect_to_login();
        }

        if (!isset($_POST['fjf_reservations_nonce']) || !wp_verify_nonce($_POST['fjf_reservations_nonce'], 'fjf_reservations_book_slot_action')) {
            wp_die('Action non autorisée (nonce invalide).');
        }

        $jour = isset($_POST['jour']) ? sanitize_text_field($_POST['jour']) : '';
        $heure = isset($_POST['heure']) ? sanitize_text_field($_POST['heure']) : '';
        $prestation = isset($_POST['prestation']) ? sanitize_text_field($_POST['prestation']) : '';

        $jours_autorises = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi');
        if (!in_array($jour, $jours_autorises, true)) {
            self::redirect_planning('error');
        }

        if (!preg_match('/^(0[8-9]|1[0-7]):00$/', $heure)) {
            self::redirect_planning('error');
        }

        if ($heure === '12:00' || $heure === '13:00') {
            self::redirect_planning('unavailable');
        }

        if ($prestation === '') {
            $prestation = 'Réservation planning';
        }

        global $wpdb;
        $table_slots = $wpdb->prefix . 'fjf_creneaux';
        $table_reservations = $wpdb->prefix . 'fjf_reservations';

        // Vérifie le statut du créneau dans fjf_creneaux.
        $slot = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, statut FROM {$table_slots} WHERE jour = %s AND heure = %s LIMIT 1",
                $jour,
                $heure
            )
        );

        if (!$slot || $slot->statut !== 'disponible') {
            self::redirect_planning('unavailable');
        }

        // Option anti-doublon: pas de seconde demande active sur le même créneau.
        $active_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_reservations} WHERE jour = %s AND heure = %s AND statut IN (%s, %s)",
                $jour,
                $heure,
                'en_attente',
                'valide'
            )
        );

        if ($active_count > 0) {
            self::redirect_planning('unavailable');
        }

        $inserted = $wpdb->insert(
            $table_reservations,
            array(
                'user_id' => get_current_user_id(),
                'prestation' => $prestation,
                'jour' => $jour,
                'heure' => $heure,
                'statut' => 'en_attente',
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );

        if (!$inserted) {
            self::redirect_planning('error');
        }

        // Synchronisation immédiate: un créneau demandé passe indisponible.
        $wpdb->update(
            $table_slots,
            array('statut' => 'indisponible'),
            array('jour' => $jour, 'heure' => $heure),
            array('%s'),
            array('%s', '%s')
        );

        self::redirect_planning('success');
    }

    /**
     * Redirige vers login en conservant la page d'origine.
     */
    private static function redirect_to_login() {
        $target = wp_get_referer() ? wp_get_referer() : home_url('/planning/');
        wp_safe_redirect(wp_login_url($target));
        exit;
    }

    /**
     * Redirige vers la page planning avec message.
     */
    private static function redirect_planning($status) {
        $planning_page = get_page_by_path('planning');
        $url = $planning_page ? get_permalink($planning_page) : home_url('/planning/');
        $url = add_query_arg('fjf_status', $status, $url);

        wp_safe_redirect($url);
        exit;
    }
}
