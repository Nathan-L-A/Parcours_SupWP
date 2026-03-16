<?php

add_action('wp_ajax_nopriv_inssetsup_login',    array('InssetSup_Actions_Auth', 'login'));
add_action('wp_ajax_inssetsup_login',            array('InssetSup_Actions_Auth', 'login'));
add_action('wp_ajax_nopriv_inssetsup_register',  array('InssetSup_Actions_Auth', 'register'));
add_action('wp_ajax_inssetsup_register',         array('InssetSup_Actions_Auth', 'register'));
add_action('wp_ajax_nopriv_inssetsup_logout',    array('InssetSup_Actions_Auth', 'logout'));
add_action('wp_ajax_inssetsup_logout',           array('InssetSup_Actions_Auth', 'logout'));

class InssetSup_Actions_Auth {

    public static function login() {
        check_ajax_referer('inssetsup_auth_nonce', 'nonce');

        $email    = isset($_POST['email'])    ? sanitize_email(wp_unslash($_POST['email']))    : '';
        $password = isset($_POST['password']) ? wp_unslash($_POST['password'])                 : '';

        if (!$email || !$password)
            wp_send_json_error(array('message' => 'Email et mot de passe requis.'));

        if (!is_email($email))
            wp_send_json_error(array('message' => 'Adresse email invalide.'));

        $student = InssetSup_Helper_Auth::find_student_by_email($email);

        if (!$student || !password_verify($password, $student->password))
            wp_send_json_error(array('message' => 'Identifiants incorrects.'));

        if ((int) $student->isarchived === 1)
            wp_send_json_error(array('message' => 'Ce compte est désactivé. Contactez l\'administration.'));

        if (!session_id())
            session_start();

        InssetSup_Helper_Auth::login_student($student->id_student);
        wp_send_json_success(array('redirect' => home_url('/campagne/')));
    }

    public static function register() {
        check_ajax_referer('inssetsup_auth_nonce', 'nonce');

        $fname    = isset($_POST['fname'])    ? sanitize_text_field(wp_unslash($_POST['fname']))    : '';
        $lname    = isset($_POST['lname'])    ? sanitize_text_field(wp_unslash($_POST['lname']))    : '';
        $email    = isset($_POST['email'])    ? sanitize_email(wp_unslash($_POST['email']))          : '';
        $password = isset($_POST['password']) ? wp_unslash($_POST['password'])                       : '';

        if (!$fname || !$lname || !$email || !$password)
            wp_send_json_error(array('message' => 'Tous les champs sont requis.'));

        if (!is_email($email))
            wp_send_json_error(array('message' => 'Adresse email invalide.'));

        if (strlen($password) < 8)
            wp_send_json_error(array('message' => 'Le mot de passe doit contenir au moins 8 caractères.'));

        if (InssetSup_Helper_Auth::find_student_by_email($email))
            wp_send_json_error(array('message' => 'Un compte existe déjà avec cette adresse email.'));

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $id   = InssetSup_Helper_Auth::create_student($fname, $lname, $email, $hash);

        if (!$id)
            wp_send_json_error(array('message' => 'Erreur lors de la création du compte. Réessayez.'));

        if (!session_id())
            session_start();

        InssetSup_Helper_Auth::login_student($id);
        wp_send_json_success(array('redirect' => home_url('/campagne/')));
    }

    public static function logout() {
        check_ajax_referer('inssetsup_auth_nonce', 'nonce');

        if (!session_id())
            session_start();

        InssetSup_Helper_Auth::logout_student();
        wp_send_json_success(array('redirect' => home_url('/')));
    }
}
