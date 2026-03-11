<?php if (!defined('ABSPATH')) exit; ?>

<?php
// Message de confirmation après envoi
if (isset($_GET['fjf_rdv']) && $_GET['fjf_rdv'] === 'ok') {
    echo '<div class="fjf-rdv-success">Votre demande de rendez-vous a bien été envoyée !</div>';
}
?>

<form class="fjf-rdv-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

    <?php wp_nonce_field('fjf_rdv_nonce_action', 'fjf_rdv_nonce'); ?>
    <input type="hidden" name="action" value="fjf_submit_rdv" />

    <div class="fjf-rdv-row">
        <div class="fjf-rdv-field">
            <label for="fjf_nom">Nom *</label>
            <input type="text" id="fjf_nom" name="nom" required />
        </div>
        <div class="fjf-rdv-field">
            <label for="fjf_prenom">Prénom *</label>
            <input type="text" id="fjf_prenom" name="prenom" required />
        </div>
    </div>

    <div class="fjf-rdv-row">
        <div class="fjf-rdv-field">
            <label for="fjf_telephone">Téléphone *</label>
            <input type="tel" id="fjf_telephone" name="telephone" required />
        </div>
        <div class="fjf-rdv-field">
            <label for="fjf_email">Email *</label>
            <input type="email" id="fjf_email" name="email" required />
        </div>
    </div>

    <div class="fjf-rdv-row">
        <div class="fjf-rdv-field">
            <label for="fjf_immat">Immatriculation <span class="fjf-optional">(facultatif)</span></label>
            <input type="text" id="fjf_immat" name="immatriculation" />
        </div>
    </div>

    <div class="fjf-rdv-row">
        <div class="fjf-rdv-field fjf-rdv-full">
            <label for="fjf_message">Message *</label>
            <textarea id="fjf_message" name="message" rows="5" required></textarea>
        </div>
    </div>

    <div class="fjf-rdv-row">
        <button type="submit" class="fjf-rdv-btn">Envoyer ma demande</button>
    </div>

</form>
