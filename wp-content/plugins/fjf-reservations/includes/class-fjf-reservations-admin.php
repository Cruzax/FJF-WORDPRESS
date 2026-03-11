<?php
/**
 * Menu et pages admin du plugin FJF Réservations.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FJF_Reservations_Admin {

    /**
     * Initialise les hooks admin.
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
        add_action('admin_post_fjf_reservations_manage', array(__CLASS__, 'handle_reservation_action'));
        add_action('admin_post_fjf_slots_manage', array(__CLASS__, 'handle_slot_action'));
    }

    /**
     * Ajoute le menu principal + sous-menus.
     */
    public static function register_menu() {
        add_menu_page(
            'FJF Réservations',
            'FJF Réservations',
            'manage_options',
            'fjf-reservations',
            array(__CLASS__, 'render_reservations_page'),
            'dashicons-calendar-alt',
            28
        );

        add_submenu_page(
            'fjf-reservations',
            'Réservations',
            'Réservations',
            'manage_options',
            'fjf-reservations',
            array(__CLASS__, 'render_reservations_page')
        );

        add_submenu_page(
            'fjf-reservations',
            'Créneaux',
            'Créneaux',
            'manage_options',
            'fjf-reservations-slots',
            array(__CLASS__, 'render_slots_page')
        );
    }

    /**
     * Page admin: Réservations.
     */
    public static function render_reservations_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fjf_reservations';

        $reservations = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC");

        $grouped = array(
            'en_attente' => array(),
            'valide'     => array(),
            'refuse'     => array(),
        );

        foreach ($reservations as $resa) {
            $status = in_array($resa->statut, array('en_attente', 'valide', 'refuse'), true) ? $resa->statut : 'en_attente';
            $grouped[$status][] = $resa;
        }

        ?>
        <div class="wrap fjf-resa-admin">
            <h1>FJF Réservations - Réservations</h1>

            <?php self::render_admin_notice(); ?>

            <p><strong>Total réservations:</strong> <?php echo esc_html(count($reservations)); ?></p>

            <?php self::render_reservations_table('Demandes en attente', $grouped['en_attente']); ?>
            <?php self::render_reservations_table('Demandes validées', $grouped['valide']); ?>
            <?php self::render_reservations_table('Demandes refusées', $grouped['refuse']); ?>
        </div>
        <?php
    }

    /**
     * Page admin: Créneaux.
     */
    public static function render_slots_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'fjf_creneaux';
        $table_resa = $wpdb->prefix . 'fjf_reservations';
        $slots = $wpdb->get_results("SELECT * FROM {$table}");

        $active_reservations = $wpdb->get_results(
            "SELECT jour, heure, statut FROM {$table_resa} WHERE statut IN ('en_attente', 'valide')",
            ARRAY_A
        );

        // Map créneau -> statut métier prioritaire (valide > en_attente)
        $active_map = array();
        foreach ($active_reservations as $resa) {
            $key = $resa['jour'] . '|' . $resa['heure'];
            if (!isset($active_map[$key])) {
                $active_map[$key] = $resa['statut'];
                continue;
            }

            // Si une réservation validée existe, elle devient prioritaire à l'affichage.
            if ($resa['statut'] === 'valide') {
                $active_map[$key] = 'valide';
            }
        }

        $slot_map = array();
        foreach ($slots as $slot) {
            $slot_map[$slot->jour . '|' . $slot->heure] = $slot;
        }

        $jours = array(
            'lundi' => 'Lundi',
            'mardi' => 'Mardi',
            'mercredi' => 'Mercredi',
            'jeudi' => 'Jeudi',
            'vendredi' => 'Vendredi',
        );

        ?>
        <div class="wrap fjf-resa-admin">
            <h1>FJF Réservations - Créneaux</h1>

            <?php self::render_admin_notice(); ?>

            <div class="fjf-admin-planning-wrap">
                <table class="widefat striped fjf-admin-planning">
                    <thead>
                        <tr>
                            <th>Heure</th>
                            <?php foreach ($jours as $jour_label) : ?>
                                <th><?php echo esc_html($jour_label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($h = 8; $h <= 17; $h++) :
                            $heure = sprintf('%02d:00', $h);
                        ?>
                            <tr>
                                <th><?php echo esc_html($heure); ?></th>

                                <?php foreach ($jours as $jour_key => $jour_label) :
                                    $key = $jour_key . '|' . $heure;

                                    if (!isset($slot_map[$key])) {
                                        continue;
                                    }

                                    $slot = $slot_map[$key];
                                    $next_status = $slot->statut === 'disponible' ? 'indisponible' : 'disponible';

                                    // Libellé affiché dans la cellule selon le vrai état métier.
                                    $display_label = $slot->statut;
                                    $display_class = $slot->statut;

                                    if (isset($active_map[$key])) {
                                        if ($active_map[$key] === 'valide') {
                                            $display_label = 'RDV validé';
                                            $display_class = 'valide';
                                        } else {
                                            $display_label = 'Demande de RDV';
                                            $display_class = 'en_attente';
                                        }
                                    }
                                ?>
                                    <td>
                                        <?php if (isset($active_map[$key]) && $active_map[$key] === 'en_attente') : ?>
                                            <a
                                                href="<?php echo esc_url(admin_url('admin.php?page=fjf-reservations')); ?>"
                                                class="button button-small fjf-btn-en-attente fjf-slot-link"
                                                title="Voir les demandes de réservation"
                                            >
                                                <?php echo esc_html($display_label); ?>
                                            </a>
                                        <?php else : ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fjf-inline-form fjf-slot-toggle-form">
                                                <input type="hidden" name="action" value="fjf_slots_manage">
                                                <input type="hidden" name="slot_id" value="<?php echo esc_attr($slot->id); ?>">
                                                <input type="hidden" name="slot_status" value="<?php echo esc_attr($next_status); ?>">
                                                <?php wp_nonce_field('fjf_slots_manage_action', 'fjf_slots_nonce'); ?>

                                                <?php
                                                $slot_button_class = 'button button-small fjf-slot-toggle-disponible';
                                                if ($display_class === 'valide') {
                                                    $slot_button_class = 'button button-small fjf-btn-valide';
                                                } elseif ($display_class === 'indisponible') {
                                                    $slot_button_class = 'button button-small fjf-btn-refuse';
                                                }
                                                ?>
                                                <button type="submit" class="<?php echo esc_attr($slot_button_class); ?>">
                                                    <?php echo esc_html($display_label); ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Affiche un tableau de réservations pour un statut donné.
     */
    private static function render_reservations_table($title, $rows) {
        ?>
        <h2><?php echo esc_html($title); ?></h2>

        <table class="widefat striped fjf-admin-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Prestation</th>
                    <th>Jour</th>
                    <th>Heure</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)) : ?>
                    <tr><td colspan="7">Aucune réservation.</td></tr>
                <?php else : ?>
                    <?php foreach ($rows as $resa) :
                        $user = get_userdata((int) $resa->user_id);
                        $client_name = $user ? $user->display_name : 'Utilisateur #' . (int) $resa->user_id;
                    ?>
                        <tr>
                            <td><?php echo esc_html($client_name); ?></td>
                            <td><?php echo esc_html($resa->prestation); ?></td>
                            <td><?php echo esc_html(ucfirst($resa->jour)); ?></td>
                            <td><?php echo esc_html($resa->heure); ?></td>
                            <td>
                                <span class="fjf-badge fjf-badge-<?php echo esc_attr($resa->statut); ?>">
                                    <?php echo esc_html($resa->statut); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($resa->created_at); ?></td>
                            <td>
                                <?php self::render_reservation_actions($resa); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Boutons admin pour une réservation.
     */
    private static function render_reservation_actions($resa) {
        $id = (int) $resa->id;
        ?>
        <div class="fjf-actions-row">
            <?php if ($resa->statut !== 'valide') : ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fjf-inline-form">
                    <input type="hidden" name="action" value="fjf_reservations_manage">
                    <input type="hidden" name="reservation_id" value="<?php echo esc_attr($id); ?>">
                    <input type="hidden" name="reservation_action" value="valider">
                    <?php wp_nonce_field('fjf_reservations_manage_action', 'fjf_reservations_nonce'); ?>
                    <button type="submit" class="button button-small fjf-btn-valide">Valider</button>
                </form>
            <?php endif; ?>

            <?php if ($resa->statut !== 'refuse') : ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fjf-inline-form">
                    <input type="hidden" name="action" value="fjf_reservations_manage">
                    <input type="hidden" name="reservation_id" value="<?php echo esc_attr($id); ?>">
                    <input type="hidden" name="reservation_action" value="refuser">
                    <?php wp_nonce_field('fjf_reservations_manage_action', 'fjf_reservations_nonce'); ?>
                    <button type="submit" class="button button-small fjf-btn-refuse">Refuser</button>
                </form>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="fjf-inline-form" onsubmit="return confirm('Supprimer cette réservation ?');">
                <input type="hidden" name="action" value="fjf_reservations_manage">
                <input type="hidden" name="reservation_id" value="<?php echo esc_attr($id); ?>">
                <input type="hidden" name="reservation_action" value="supprimer">
                <?php wp_nonce_field('fjf_reservations_manage_action', 'fjf_reservations_nonce'); ?>
                <button type="submit" class="button button-small button-link-delete">Supprimer</button>
            </form>
        </div>
        <?php
    }

    /**
     * Traitement des actions admin sur réservations.
     */
    public static function handle_reservation_action() {
        if (!current_user_can('manage_options')) {
            wp_die('Accès interdit.');
        }

        if (!isset($_POST['fjf_reservations_nonce']) || !wp_verify_nonce($_POST['fjf_reservations_nonce'], 'fjf_reservations_manage_action')) {
            wp_die('Nonce invalide.');
        }

        $reservation_id = isset($_POST['reservation_id']) ? absint($_POST['reservation_id']) : 0;
        $action_type = isset($_POST['reservation_action']) ? sanitize_text_field($_POST['reservation_action']) : '';

        if (!$reservation_id || !in_array($action_type, array('valider', 'refuser', 'supprimer'), true)) {
            self::redirect_admin('error');
        }

        global $wpdb;
        $table_resa = $wpdb->prefix . 'fjf_reservations';
        $table_slots = $wpdb->prefix . 'fjf_creneaux';

        $resa = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_resa} WHERE id = %d", $reservation_id));
        if (!$resa) {
            self::redirect_admin('notfound');
        }

        if ($action_type === 'valider') {
            $wpdb->update($table_resa, array('statut' => 'valide'), array('id' => $reservation_id), array('%s'), array('%d'));
            $wpdb->update(
                $table_slots,
                array('statut' => 'indisponible'),
                array('jour' => $resa->jour, 'heure' => $resa->heure),
                array('%s'),
                array('%s', '%s')
            );
            self::redirect_admin('updated');
        }

        if ($action_type === 'refuser') {
            $wpdb->update($table_resa, array('statut' => 'refuse'), array('id' => $reservation_id), array('%s'), array('%d'));

            // Recalcule le statut du créneau selon les réservations actives.
            self::sync_slot_status($resa->jour, $resa->heure);
            self::redirect_admin('updated');
        }

        if ($action_type === 'supprimer') {
            $wpdb->delete($table_resa, array('id' => $reservation_id), array('%d'));

            // Recalcule le statut du créneau après suppression.
            self::sync_slot_status($resa->jour, $resa->heure);
            self::redirect_admin('deleted');
        }

        self::redirect_admin('error');
    }

    /**
     * Traitement des actions admin sur créneaux.
     */
    public static function handle_slot_action() {
        if (!current_user_can('manage_options')) {
            wp_die('Accès interdit.');
        }

        if (!isset($_POST['fjf_slots_nonce']) || !wp_verify_nonce($_POST['fjf_slots_nonce'], 'fjf_slots_manage_action')) {
            wp_die('Nonce invalide.');
        }

        $slot_id = isset($_POST['slot_id']) ? absint($_POST['slot_id']) : 0;
        $slot_status = isset($_POST['slot_status']) ? sanitize_text_field($_POST['slot_status']) : '';

        if (!$slot_id || !in_array($slot_status, array('disponible', 'indisponible'), true)) {
            self::redirect_slots('error');
        }

        global $wpdb;
        $table_slots = $wpdb->prefix . 'fjf_creneaux';

        // Sécurité cohérence: impossible de remettre disponible si une réservation
        // active existe déjà sur ce créneau.
        if ($slot_status === 'disponible') {
            $slot = $wpdb->get_row($wpdb->prepare("SELECT jour, heure FROM {$table_slots} WHERE id = %d", $slot_id));
            if ($slot && self::has_active_reservation($slot->jour, $slot->heure)) {
                self::redirect_slots('locked');
            }
        }

        $wpdb->update(
            $table_slots,
            array('statut' => $slot_status),
            array('id' => $slot_id),
            array('%s'),
            array('%d')
        );

        self::redirect_slots('updated');
    }


    /**
     * Notice simple sur pages admin du plugin.
     */
    private static function render_admin_notice() {
        if (!isset($_GET['fjf_msg'])) {
            return;
        }

        $msg = sanitize_text_field($_GET['fjf_msg']);
        $map = array(
            'updated'  => array('notice-success', 'Mise à jour effectuée.'),
            'deleted'  => array('notice-success', 'Élément supprimé.'),
            'notfound' => array('notice-warning', 'Élément introuvable.'),
            'locked'   => array('notice-warning', 'Impossible de rendre disponible: une réservation active existe sur ce créneau.'),
            'error'    => array('notice-error', 'Une erreur est survenue.'),
        );

        if (!isset($map[$msg])) {
            return;
        }

        ?>
        <div class="notice <?php echo esc_attr($map[$msg][0]); ?> is-dismissible"><p><?php echo esc_html($map[$msg][1]); ?></p></div>
        <?php
    }

    /**
     * Redirections helpers.
     */
    private static function redirect_admin($msg) {
        $url = add_query_arg(
            array(
                'page'    => 'fjf-reservations',
                'fjf_msg' => $msg,
            ),
            admin_url('admin.php')
        );
        wp_safe_redirect($url);
        exit;
    }

    private static function redirect_slots($msg) {
        $url = add_query_arg(
            array(
                'page'    => 'fjf-reservations-slots',
                'fjf_msg' => $msg,
            ),
            admin_url('admin.php')
        );
        wp_safe_redirect($url);
        exit;
    }

    /**
     * Vérifie si un créneau possède une réservation active.
     */
    private static function has_active_reservation($jour, $heure) {
        global $wpdb;
        $table_resa = $wpdb->prefix . 'fjf_reservations';

        $count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_resa} WHERE jour = %s AND heure = %s AND statut IN (%s, %s)",
                $jour,
                $heure,
                'en_attente',
                'valide'
            )
        );

        return $count > 0;
    }

    /**
     * Synchronise le statut d'un créneau selon les réservations actives.
     */
    private static function sync_slot_status($jour, $heure) {
        global $wpdb;
        $table_slots = $wpdb->prefix . 'fjf_creneaux';

        $new_status = self::has_active_reservation($jour, $heure) ? 'indisponible' : 'disponible';

        $wpdb->update(
            $table_slots,
            array('statut' => $new_status),
            array('jour' => $jour, 'heure' => $heure),
            array('%s'),
            array('%s', '%s')
        );
    }

}
