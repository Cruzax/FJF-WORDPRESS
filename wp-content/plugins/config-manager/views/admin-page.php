<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="config-manager">
    <div class="container">
        <h1 class="py-4">Gestionnaire de configuration</h1>    
        <?php if ($message) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo esc_html($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
        <div class="row">
            <form method="post">
                <div class="row mb-3">
                    <label for="inputTel" class="col-sm-3 col-form-label">N° de téléphone</label>
                    <div class="col-sm-9">
                        <input type="text" name="telephone" class="form-control" id="inputTel" 
                        value="<?php echo $configs['telephone'] ?? '';?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="inputEmail" class="col-sm-3 col-form-label">Adresse email</label>
                    <div class="col-sm-9">
                        <input type="email" name="email" class="form-control" id="inputEmail" 
                        value="<?php echo $configs['email'] ?? ''; ?>">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" name="save_config" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>