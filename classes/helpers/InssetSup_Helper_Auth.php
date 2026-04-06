<?php

/**
 * Helper d'authentification pour les étudiants.
 *
 * Les étudiants ne sont PAS des utilisateurs WordPress : leur identité
 * est stockée dans wp_inssetsup_student et persistants en session PHP native.
 *
 * La clé de session SESSION_KEY contient l'id_student de l'étudiant connecté.
 */

class InssetSup_Helper_Auth {

    // Clé utilisée dans $_SESSION pour identifier l'étudiant connecté.
    const SESSION_KEY = 'inssetsup_student_id';

    /** Vérifie si un étudiant est connecté (session active). */
    public static function is_student_logged_in() {
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    /** Retourne l'id_student de l'étudiant connecté, ou null si aucun. */
    public static function get_current_student_id() {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    private static function table($name) {
        global $wpdb;
        return $wpdb->prefix . 'inssetsup_' . $name;
    }

    /**
     * Retourne l'objet étudiant complet depuis la DB.
     * Limite la sélection aux colonnes nécessaires (pas le hash du mot de passe).
     * Retourne null si la session est vide ou si l'étudiant est introuvable.
     */
    public static function get_current_student() {
        $id = self::get_current_student_id();
        if (!$id)
            return null;

        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `id_student`, `fname_student`, `lname_student`, `email_student`, `isactivated`, `isarchived`
                 FROM `" . self::table('student') . "`
                 WHERE `id_student` = %s",
                $id
            )
        );
    }

    public static function find_student_by_email($email) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `" . self::table('student') . "` WHERE `email_student` = %s",
                sanitize_email($email)
            )
        );
    }

    public static function create_student($fname, $lname, $email, $password_hash) {
        global $wpdb;
        $id  = uniqid('stu_', true);
        $now = current_time('mysql');

        $wpdb->insert(
            self::table('student'),
            array(
                'id_student'    => $id,
                'fname_student' => $fname,
                'lname_student' => $lname,
                'email_student' => $email,
                'password'      => $password_hash,
                'created_at'    => $now,
                'updated_at'    => $now,
                'isactivated'   => 1,
                'isarchived'    => 0,
            ),
            array('%s','%s','%s','%s','%s','%s','%s','%d','%d')
        );

        return empty($wpdb->last_error) ? $id : false;
    }

    /**
     * Démarre la session étudiant en stockant son ID.
     * Appeler APRÈS avoir vérifié les identifiants.
     */
    public static function login_student($id) {
        $_SESSION[self::SESSION_KEY] = $id;
    }

    /** Détruit la session étudiant (déconnexion). */
    public static function logout_student() {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
