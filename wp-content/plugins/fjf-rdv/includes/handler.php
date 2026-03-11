<?php
/**
 * Traitement du formulaire de rendez-vous.
 * Reçoit les données via admin-post.php, les nettoie et les enregistre en BDD.
 */

if (!defined('ABSPATH')) exit;

function fjf_rdv_handle_form() {
    // 1. Vérifier le nonce (sécurité anti-CSRF)
    if (!isset($_POST['fjf_rdv_nonce']) || !wp_verify_nonce($_POST['fjf_rdv_nonce'], 'fjf_rdv_nonce_action')) {
        wp_die('Sécurité : requête non autorisée.');
    }

    // 2. Nettoyer les données
    $nom              = sanitize_text_field($_POST['nom'] ?? '');
    $prenom           = sanitize_text_field($_POST['prenom'] ?? '');
    $telephone        = sanitize_text_field($_POST['telephone'] ?? '');
    $email            = sanitize_email($_POST['email'] ?? '');
    $immatriculation  = sanitize_text_field($_POST['immatriculation'] ?? '');
    $message          = sanitize_textarea_field($_POST['message'] ?? '');

    // 3. Vérifier que les champs obligatoires sont remplis
    if (!$nom || !$telephone || !$email || !$message) {
        wp_die('Veuillez remplir tous les champs obligatoires.');
    }

    // 4. Enregistrer en base de données
    global $wpdb;
    $table_name = $wpdb->prefix . 'fjf_rdv';

    $wpdb->insert($table_name, array(
        'nom'              => $nom,
        'prenom'           => $prenom,
        'telephone'        => $telephone,
        'email'            => $email,
        'immatriculation'  => $immatriculation,
        'message'          => $message,
    ), array('%s', '%s', '%s', '%s', '%s', '%s'));

    // 5. Rediriger vers la page d'origine avec un message de succès
    $redirect_url = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : wp_get_referer();
    if (!$redirect_url) {
        $redirect_url = home_url('/');
    }
    $redirect_url = add_query_arg('fjf_rdv', 'ok', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
}

// Accessible aux visiteurs connectés ET non connectés
add_action('admin_post_fjf_submit_rdv', 'fjf_rdv_handle_form');
add_action('admin_post_nopriv_fjf_submit_rdv', 'fjf_rdv_handle_form');
