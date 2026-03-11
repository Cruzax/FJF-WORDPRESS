<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap voiture-wrap">
    <h1>Voiture Clients</h1>

    <h2>Ajouter un véhicule</h2>
    <form method="post">
        <?php wp_nonce_field('voiture_add_entry', 'voiture_nonce'); ?>
        <div class="voiture-form-row">
            <div class="voiture-field">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required />
            </div>
            <div class="voiture-field">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required />
            </div>
            <div class="voiture-field">
                <label for="telephone">Numéro de téléphone</label>
                <input type="tel" id="telephone" name="telephone" required />
            </div>
        </div>
        <div class="voiture-form-row">
            <div class="voiture-field">
                <label for="immatriculation">Immatriculation</label>
                <input type="text" id="immatriculation" name="immatriculation" required />
            </div>
            <div class="voiture-field">
                <label for="marque_modele">Marque / Modèle</label>
                <input type="text" id="marque_modele" name="marque_modele" required />
            </div>
            <div class="voiture-field">
                <label for="km">Kilométrage (KM)</label>
                <input type="number" id="km" name="km" min="0" required />
            </div>
            <div class="voiture-field">
                <label for="annee">Année</label>
                <input type="number" id="annee" name="annee" min="1900" max="<?php echo esc_attr(date('Y')); ?>" required />
            </div>
        </div>
        <div class="voiture-form-row">
            <div class="voiture-field voiture-field-full">
                <label for="commentaire">Commentaire <span class="description">(facultatif)</span></label>
                <textarea id="commentaire" name="commentaire" rows="3"></textarea>
            </div>
        </div>
        <p class="submit">
            <input type="submit" name="voiture_submit" class="button button-primary" value="Ajouter" />
        </p>
    </form>

    <?php if ($entries) : ?>
    <h2>Véhicules enregistrés</h2>
    <table class="widefat fixed striped" id="voiture-table">
        <thead>
            <tr>
                <th class="sortable" data-col="0" data-type="num">ID <span class="sort-arrow"></span></th>
                <th class="sortable" data-col="1" data-type="str">Nom <span class="sort-arrow"></span></th>
                <th class="sortable" data-col="2" data-type="str">Prénom <span class="sort-arrow"></span></th>
                <th>Téléphone</th>
                <th class="sortable" data-col="4" data-type="num">KM <span class="sort-arrow"></span></th>
                <th class="sortable" data-col="5" data-type="str">Marque / Modèle <span class="sort-arrow"></span></th>
                <th class="sortable" data-col="6" data-type="str">Immat <span class="sort-arrow"></span></th>
                <th class="sortable" data-col="7" data-type="num">Année <span class="sort-arrow"></span></th>
                <th>Commentaire</th>
                <th class="sortable" data-col="9" data-type="str">Date d'ajout <span class="sort-arrow"></span></th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entries as $entry) : ?>
            <tr>
                <td><?php echo esc_html($entry->id); ?></td>
                <td><?php echo esc_html($entry->nom); ?></td>
                <td><?php echo esc_html($entry->prenom); ?></td>
                <td><?php echo esc_html($entry->telephone); ?></td>
                <td><?php echo esc_html(number_format($entry->km, 0, ',', ' ')); ?> km</td>
                <td><?php echo esc_html($entry->marque_modele); ?></td>
                    <td><?php echo esc_html($entry->immatriculation); ?></td>
                    <td><?php echo esc_html($entry->annee); ?></td>
                    <td><?php echo esc_html($entry->commentaire); ?></td>
                    <td><?php echo esc_html($entry->created_at); ?></td>
                <td>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=voiture-clients&delete_id=' . $entry->id), 'voiture_delete_' . $entry->id)); ?>"
                       class="button button-small"
                       onclick="return confirm('Supprimer ce véhicule ?');">
                        Supprimer
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p>Aucun véhicule enregistré pour le moment.</p>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('voiture-table');
    if (!table) return;
    var headers = table.querySelectorAll('th.sortable');
    var currentCol = -1;
    var ascending = true;

    headers.forEach(function(header) {
        header.addEventListener('click', function() {
            var col = parseInt(this.dataset.col);
            var type = this.dataset.type;

            if (currentCol === col) {
                ascending = !ascending;
            } else {
                ascending = true;
                currentCol = col;
            }

            table.querySelectorAll('.sort-arrow').forEach(function(el) {
                el.className = 'sort-arrow';
            });
            this.querySelector('.sort-arrow').className = 'sort-arrow ' + (ascending ? 'asc' : 'desc');

            var tbody = table.querySelector('tbody');
            var rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort(function(a, b) {
                var aVal = a.cells[col].textContent.trim();
                var bVal = b.cells[col].textContent.trim();

                if (type === 'num') {
                    aVal = parseFloat(aVal.replace(/[^\d.-]/g, '')) || 0;
                    bVal = parseFloat(bVal.replace(/[^\d.-]/g, '')) || 0;
                    return ascending ? aVal - bVal : bVal - aVal;
                } else {
                    return ascending
                        ? aVal.localeCompare(bVal, 'fr')
                        : bVal.localeCompare(aVal, 'fr');
                }
            });

            rows.forEach(function(row) { tbody.appendChild(row); });
        });
    });
});
</script>