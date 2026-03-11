<?php
/**
 * Vue admin — affiche les demandes dans 3 tableaux séparés selon le statut.
 * Les variables $en_attente, $valides et $refuses viennent de admin-page.php.
 */
if (!defined('ABSPATH')) exit;

/**
 * Fonction utilitaire : affiche un tableau HTML pour une liste de demandes.
 * On l'appelle 3 fois (une par statut) pour éviter de répéter le même HTML.
 *
 * @param array $demandes  Les lignes à afficher (résultat d'un $wpdb->get_results).
 */
if (!function_exists('fjf_rdv_afficher_tableau')) {
    function fjf_rdv_afficher_tableau($demandes) {

        // Correspondance valeur BDD → texte affiché
        $labels = array(
            'en_attente' => 'En attente',
            'valide'     => 'Validé',
            'refuse'     => 'Refusé',
        );

        // Boutons disponibles avec leur libellé et leur classe CSS
        $tous_boutons = array(
            'en_attente' => array('label' => 'En attente', 'classe' => 'fjf-btn-en-attente'),
            'valide'     => array('label' => 'Validé',     'classe' => 'fjf-btn-valide'),
            'refuse'     => array('label' => 'Refusé',     'classe' => 'fjf-btn-refuse'),
        );

        if (empty($demandes)) {
            echo '<p class="fjf-vide">Aucune demande dans cette catégorie.</p>';
            return;
        }
        ?>
        <table class="widefat fixed striped fjf-admin-table">
            <thead>
                <tr>
                    <th class="col-client">Client</th>
                    <th class="col-tel">Téléphone</th>
                    <th class="col-email">Email</th>
                    <th class="col-immat">Immat</th>
                    <th class="col-msg">Message</th>
                    <th class="col-date">Date</th>
                    <th class="col-statut">Statut</th>
                    <th class="col-action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demandes as $d) :
                    // Statut de la ligne ("en_attente" par défaut si la colonne est vide)
                    $statut = !empty($d->statut) ? $d->statut : 'en_attente';
                    $label  = isset($labels[$statut]) ? $labels[$statut] : 'En attente';

                    // Extrait du message : 45 caractères max, avec "..." si trop long
                    $extrait = mb_strlen($d->message) > 45
                        ? mb_substr($d->message, 0, 45) . '...'
                        : $d->message;

                    // Identifiant unique pour la ligne de détail
                    $detail_id = 'fjf-detail-' . $d->id;
                ?>
                <!-- Ligne principale -->
                <tr>
                    <!-- Prénom + Nom fusionnés dans la colonne "Client" -->
                    <td><?php echo esc_html($d->prenom . ' ' . $d->nom); ?></td>
                    <td><?php echo esc_html($d->telephone); ?></td>
                    <td><?php echo esc_html($d->email); ?></td>
                    <td><?php echo esc_html($d->immatriculation); ?></td>

                    <!-- Extrait du message + bouton pour déplier la ligne de détail -->
                    <td class="fjf-msg-preview">
                        <span class="fjf-extrait"><?php echo esc_html($extrait); ?></span>
                        <?php if (mb_strlen($d->message) > 45) : ?>
                            <button
                                type="button"
                                class="fjf-btn-voir"
                                onclick="fjfToggleDetail('<?php echo esc_js($detail_id); ?>', this)">
                                Voir
                            </button>
                        <?php endif; ?>
                    </td>

                    <td><?php echo esc_html($d->created_at); ?></td>

                    <!-- Badge de statut coloré -->
                    <td class="fjf-statut-cell">
                        <span class="fjf-statut fjf-statut-<?php echo esc_attr($statut); ?>">
                            <?php echo esc_html($label); ?>
                        </span>
                    </td>

                    <!-- Boutons d'action -->
                    <td class="fjf-actions">

                        <?php
                        // On affiche uniquement les boutons des AUTRES statuts
                        // (inutile de proposer le statut déjà actif sur la ligne)
                        foreach ($tous_boutons as $valeur => $btn) :
                            if ($valeur === $statut) continue;
                        ?>
                            <a href="<?php echo esc_url(wp_nonce_url(
                                    admin_url('admin.php?page=fjf-rdv&set_status=' . $valeur . '&id=' . $d->id),
                                    'fjf_rdv_status_' . $d->id
                                )); ?>"
                               class="button button-small <?php echo esc_attr($btn['classe']); ?>">
                                <?php echo esc_html($btn['label']); ?>
                            </a>
                        <?php endforeach; ?>

                        <!-- Bouton Supprimer -->
                        <a href="<?php echo esc_url(wp_nonce_url(
                                admin_url('admin.php?page=fjf-rdv&delete_id=' . $d->id),
                                'fjf_rdv_delete_' . $d->id
                            )); ?>"
                           class="button button-small button-link-delete"
                           onclick="return confirm('Supprimer cette demande ?');">
                            Supprimer
                        </a>

                    </td>
                </tr>

                <!-- Ligne de détail : masquée par défaut, dépliée au clic sur "Voir" -->
                <tr id="<?php echo esc_attr($detail_id); ?>" class="fjf-detail-row" style="display:none;">
                    <td colspan="8" class="fjf-detail-cell">
                        <strong>Message complet :</strong><br>
                        <?php echo nl2br(esc_html($d->message)); ?>
                    </td>
                </tr>

                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Le script n'est émis qu'une seule fois, même si la fonction
        // est appelée plusieurs fois sur la même page (3 tableaux).
        static $script_emis = false;
        if (!$script_emis) :
            $script_emis = true;
        ?>
        <script>
        /**
         * Affiche ou masque la ligne de détail d'une demande.
         * @param {string} id       - L'id de la ligne <tr> à basculer.
         * @param {HTMLElement} btn - Le bouton cliqué (pour changer son texte).
         */
        function fjfToggleDetail(id, btn) {
            var row = document.getElementById(id);
            if (!row) return;

            if (row.style.display === 'none') {
                row.style.display = '';     // on affiche
                btn.textContent = 'Masquer';
            } else {
                row.style.display = 'none'; // on masque
                btn.textContent = 'Voir';
            }
        }
        </script>
        <?php endif; ?>
        <?php
    } // fin fjf_rdv_afficher_tableau()
}
?>

<div class="wrap">
    <h1>FJF Rendez-vous — Demandes reçues</h1>

    <!-- ===================================================
         1. Demandes en attente
    =================================================== -->
    <h2 class="fjf-section-title fjf-titre-en-attente">⏳ Demandes en attente</h2>
    <?php fjf_rdv_afficher_tableau($en_attente); ?>

    <!-- ===================================================
         2. Demandes validées
    =================================================== -->
    <h2 class="fjf-section-title fjf-titre-valide">✅ Demandes validées</h2>
    <?php fjf_rdv_afficher_tableau($valides); ?>

    <!-- ===================================================
         3. Demandes refusées
    =================================================== -->
    <h2 class="fjf-section-title fjf-titre-refuse">❌ Demandes refusées</h2>
    <?php fjf_rdv_afficher_tableau($refuses); ?>

</div>
