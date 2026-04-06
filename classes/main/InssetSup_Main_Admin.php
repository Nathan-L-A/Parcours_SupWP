<?php

/**
 * Contrôleur principal du back-office WordPress.
 *
 * Enregistre les menus d'administration, charge les assets (CSS/JS + nonce AJAX)
 * et délègue l'affichage des pages aux vues dédiées.
 *
 * Pages gérées :
 *   - InssetSup → Formations  (menu principal)
 *   - InssetSup → Campagnes   (sous-menu)
 */

class InssetSup_Main_Admin {

    public function __construct() {
        add_action('admin_menu',            array($this, 'register_menus'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('current_screen',        array($this, 'remove_theme_notices'));
    }

    /**
     * Supprime les notices parasites du thème Kadence sur les pages InssetSup.
     * Ces notices (« activer la licence », etc.) polluent l'interface admin.
     */
    public function remove_theme_notices() {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'inssetsup') === false)
            return;

        // Retire les notices du thème Kadence sur les pages InssetSup
        global $wp_filter;
        if (isset($wp_filter['admin_notices'])) {
            foreach ($wp_filter['admin_notices']->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $key => $cb) {
                    $fn = $cb['function'];
                    // Cible les méthodes de classes Kadence
                    if (is_array($fn) && is_object($fn[0])) {
                        $class = get_class($fn[0]);
                        if (stripos($class, 'kadence') !== false || stripos($class, 'Component') !== false)
                            remove_action('admin_notices', $fn, $priority);
                    }
                    if (is_string($fn) && stripos($fn, 'kadence') !== false)
                        remove_action('admin_notices', $fn, $priority);
                }
            }
        }
    }

    public function register_menus() {
        add_menu_page(
            'InssetSup',
            'InssetSup',
            'manage_options',
            'inssetsup',
            array($this, 'page_choices'),
            'dashicons-graduation-cap',
            26
        );

        add_submenu_page(
            'inssetsup',
            'InssetSup — Formations',
            'Formations',
            'manage_options',
            'inssetsup',
            array($this, 'page_choices')
        );

        add_submenu_page(
            'inssetsup',
            'InssetSup — Campagnes',
            'Campagnes',
            'manage_options',
            'inssetsup_campaigns',
            array($this, 'page_campaigns')
        );
    }

    /**
     * Charge CSS et JS admin uniquement sur les pages InssetSup.
     * Injecte 'InssetsupAdmin' (ajax_url + nonce) pour les appels AJAX.
     *
     * @param string $hook  Identifiant de la page admin courante.
     */
    public function assets($hook) {
        if (strpos($hook, 'inssetsup') === false)
            return;

        $css_rel  = 'InssetSup/assets/css/admin.css';
        $css_file = INSSETSUP_DIR . '/assets/css/admin.css';
        if (file_exists($css_file))
            wp_enqueue_style('inssetsup-admin', plugins_url($css_rel), array(), filemtime($css_file));

        $js_rel  = 'InssetSup/assets/js/admin.js';
        $js_file = INSSETSUP_DIR . '/assets/js/admin.js';
        if (file_exists($js_file)) {
            wp_enqueue_script('inssetsup-admin', plugins_url($js_rel), array('jquery'), filemtime($js_file), true);
            wp_localize_script('inssetsup-admin', 'InssetsupAdmin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('inssetsup_admin_nonce'),
            ));
        }
    }

    public function page_choices() {
        $view = new InssetSup_View_Choices();
        $view->render();
    }

    public function page_campaigns() {
        $view = new InssetSup_View_Campaigns();
        $view->render();
    }
}
