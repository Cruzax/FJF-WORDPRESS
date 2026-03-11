<?php
/**
 * Traitement de la reservation d'un creneau depuis le planning.
 * Envoi en POST via admin-post.php.
 */

if (!defined('ABSPATH')) exit;

/**
 * Verifie si le creneau est reservable.
 * Regle: pas de doublon en statut en_attente/valide sur le meme jour+heure.
 */
function fjf_rdv_creneau_est_disponible($jour, $heure) {
    global $wpdb;

    $table = $wpdb->prefix . 'fjf_demandes_rdv';

    $nb = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE jour = %s AND heure = %s AND statut IN (%s, %s)",
            $jour,
            $heure,
            'en_attente',
            'valide'
        )
    );

    return $nb === 0;
}

/**
 * Action principale: enregistre une demande de RDV depuis un clic sur creneau.
 */
function fjf_rdv_reserver_creneau() {
    // Si non connecte: redirection vers login
    if (!is_user_logged_in()) {
        $redirect_after_login = wp_get_referer() ? wp_get_referer() : home_url('/planning/');
        wp_safe_redirect(wp_login_url($redirect_after_login));
        exit;
    }

    // Nonce obligatoire (securite CSRF)
    if (!isset($_POST['fjf_reserver_nonce']) || !wp_verify_nonce($_POST['fjf_reserver_nonce'], 'fjf_reserver_creneau_action')) {
        wp_die('Action non autorisee (nonce invalide).');
    }

    // Donnees envoyees depuis le mini formulaire
    $jour  = isset($_POST['jour']) ? sanitize_text_field($_POST['jour']) : '';
    $heure = isset($_POST['heure']) ? sanitize_text_field($_POST['heure']) : '';

    // Liste blanche des jours autorises
    $jours_autorises = array('lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi');

    // Format attendu pour l'heure: HH:00 (08:00 a 17:00)
    $heure_valide = preg_match('/^(0[8-9]|1[0-7]):00$/', $heure);

    if (!in_array($jour, $jours_autorises, true) || !$heure_valide) {
        fjf_rdv_redirect_planning('erreur');
    }

    // Pause midi indisponible
    if ($heure === '12:00' || $heure === '13:00') {
        fjf_rdv_redirect_planning('indisponible');
    }

    // Verification disponibilite SQL
    if (!fjf_rdv_creneau_est_disponible($jour, $heure)) {
        fjf_rdv_redirect_planning('indisponible');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'fjf_demandes_rdv';

    $wpdb->insert(
        $table,
        array(
            'user_id' => get_current_user_id(),
            'jour'    => $jour,
            'heure'   => $heure,
            'statut'  => 'en_attente',
        ),
        array('%d', '%s', '%s', '%s')
    );

    if ($wpdb->last_error) {
        fjf_rdv_redirect_planning('erreur');
    }

    fjf_rdv_redirect_planning('ok');
}

/**
 * Redirige vers planning avec message flash simple via query arg.
 */
function fjf_rdv_redirect_planning($etat) {
    $planning_page = get_page_by_path('planning');
    $url = $planning_page ? get_permalink($planning_page) : home_url('/planning/');
    $url = add_query_arg('rdv_status', $etat, $url);

    wp_safe_redirect($url);
    exit;
}

add_action('admin_post_fjf_reserver_creneau', 'fjf_rdv_reserver_creneau');
add_action('admin_post_nopriv_fjf_reserver_creneau', 'fjf_rdv_reserver_creneau');
