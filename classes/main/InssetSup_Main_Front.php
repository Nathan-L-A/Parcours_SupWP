<?php

class InssetSup_Main_Front {

    public function __construct() {
        add_action('init', array($this, 'start_session'), 1);
        add_action('init', array($this, 'maybe_create_campaign_page'), 5);
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'assets'), 999);
        add_action('template_redirect', array($this, 'auth_redirect'));
    }

    public function start_session() {
        if (!session_id())
            session_start();
    }

    public function register_shortcodes() {
        add_shortcode('inssetsup_auth',     array('InssetSup_Shortcode_Auth',     'render'));
        add_shortcode('inssetsup_campagne', array('InssetSup_Shortcode_Campaign', 'render'));
    }

    /**
     * Crée la page "Campagne" avec le shortcode [inssetsup_campagne] si elle n'existe pas encore.
     * Utilise une option WP pour ne pas faire de requête DB à chaque chargement.
     */
    public function maybe_create_campaign_page() {
        if (get_option('inssetsup_campaign_page_id'))
            return;

        $page_id = wp_insert_post(array(
            'post_title'   => 'Campagne',
            'post_name'    => 'campagne',
            'post_content' => '[inssetsup_campagne]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ));

        if (!is_wp_error($page_id) && $page_id) {
            update_post_meta($page_id, '_inssetsup_page', 'campaign');
            update_option('inssetsup_campaign_page_id', $page_id);
        }
    }

    public function auth_redirect() {
        if (wp_doing_ajax())
            return;

        if (InssetSup_Helper_Auth::is_student_logged_in())
            return;

        if (is_front_page())
            return;

        wp_redirect(home_url('/'), 302);
        exit;
    }

    public function assets() {
        $base = 'InssetSup';

        // ── Login assets ──────────────────────
        $css_rel  = $base . '/assets/css/login.css';
        $css_file = INSSETSUP_DIR . '/assets/css/login.css';

        if (file_exists($css_file))
            wp_enqueue_style('inssetsup-login', plugins_url($css_rel), array(), filemtime($css_file));

        $js_rel  = $base . '/assets/js/login.js';
        $js_file = INSSETSUP_DIR . '/assets/js/login.js';

        if (file_exists($js_file)) {
            wp_enqueue_script('inssetsup-login', plugins_url($js_rel), array('jquery'), filemtime($js_file), true);
            wp_localize_script('inssetsup-login', 'InssetsupAuth', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('inssetsup_auth_nonce'),
            ));
        }

        // ── Campaign assets ───────────────────
        $camp_css_rel  = $base . '/assets/css/campaign.css';
        $camp_css_file = INSSETSUP_DIR . '/assets/css/campaign.css';

        if (file_exists($camp_css_file))
            wp_enqueue_style('inssetsup-campaign', plugins_url($camp_css_rel), array(), filemtime($camp_css_file));

        $camp_js_rel  = $base . '/assets/js/campaign.js';
        $camp_js_file = INSSETSUP_DIR . '/assets/js/campaign.js';

        if (file_exists($camp_js_file)) {
            wp_enqueue_script('inssetsup-campaign', plugins_url($camp_js_rel), array('jquery'), filemtime($camp_js_file), true);

            // Formations de la campagne demandée (uniquement si GET campaign_id + étudiant connecté)
            $campaign_id     = isset($_GET['campaign_id']) ? sanitize_text_field(wp_unslash($_GET['campaign_id'])) : '';
            $formations_data = array();

            if ($campaign_id && InssetSup_Helper_Auth::is_student_logged_in()) {
                $formations = InssetSup_Crud_StudentChoice::get_campaign_formations($campaign_id);
                foreach ($formations as $f)
                    $formations_data[] = array('id' => $f->id_choice, 'name' => $f->name_choice);
            }

            wp_localize_script('inssetsup-campaign', 'InssetsupCampaign', array(
                'ajax_url'    => admin_url('admin-ajax.php'),
                'nonce'       => wp_create_nonce('inssetsup_campaign_nonce'),
                'formations'  => $formations_data,
                'campaign_id' => $campaign_id,
            ));
        }
    }
}
