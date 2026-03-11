<?php
/**
 * Plugin Name: Config Manager
 * Description: Module WordPress permettant d'ajouter des informations dans la table options de Wordpress
 * Version: 1.0
 * Author: Cruz
 */

// Sécurité : empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale du plugin
 */
class ConfigManagerPlugin
{
    private $prefix = 'config_manager_';
    
    public function __construct()
    {        
        // Hooks WordPress
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_init', [$this, 'handleActions']);
    }
    
    // Ajouter le menu dans l'admin
    public function addAdminMenu()
    {
        add_menu_page(
            'Config Manager',           
            'Config Manager',           
            'manage_options',           
            'config-manager',           
            [$this, 'renderAdminPage'],
            'dashicons-admin-settings',
            30
        );
    }
    
    public function enqueueAssets($hook)
    {
        if ($hook !== 'toplevel_page_config-manager') {
            return;
        }
        
        // Bootstrap 5 CSS
        wp_enqueue_style(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
            [],
            '5.3.2'
        );
        
        // CSS personnalisé
        wp_enqueue_style(
            'config-manager-admin',
            plugin_dir_url(__FILE__).'/assets/css/admin.css',
            ['bootstrap'],
            '1.0.0'
        );
        
        // Bootstrap 5 JS
        wp_enqueue_script(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            [],
            '5.3.2',
            true
        );
    }

    public function renderAdminPage()
    {        
        $configs = $this->getAllConfigs();
        $message = $this->getMessage();
        include 'views/admin-page.php';
    }

    private function getAllConfigs()
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value 
                FROM {$wpdb->options} 
                WHERE option_name LIKE %s",
                $wpdb->esc_like($this->prefix) . '%'
            )
        );
        foreach ($results as $row) {
            $key = str_replace($this->prefix, '', $row->option_name);
            $configs[$key] = $row->option_value;
        }
        return $configs;
    }

    private function getMessage()
    {
        $message = get_transient('config_manager_message');
        if ($message) {
            delete_transient('config_manager_message');
            return $message;
        }
        return null;
    }

    public function handleActions()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_POST['save_config'])) {
            foreach ($_POST as $key => $value) {
                if ($key !== 'save_config') {
                    update_option($this->prefix.$key, $value);
                }
            }
            set_transient('config_manager_message', 'Config mise à jour avec succès', 30);
        }
    }

}

// Démarrer le plugin
new ConfigManagerPlugin();