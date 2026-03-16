<?php

class InssetSup_Main_Front {

    public function __construct() {
        add_action('init', array($this, 'start_session'), 1);
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'assets'), 999);
        add_action('template_redirect', array($this, 'auth_redirect'));
    }

    public function start_session() {
        if (!session_id())
            session_start();
    }

    public function register_shortcodes() {
        add_shortcode('inssetsup_auth', array('InssetSup_Shortcode_Auth', 'render'));
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
        $base     = 'InssetSup';
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
    }
}
