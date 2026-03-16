<?php

class InssetSup_Helper_Auth {

    const SESSION_KEY = 'inssetsup_student_id';

    public static function is_student_logged_in() {
        return !empty($_SESSION[self::SESSION_KEY]);
    }

    public static function get_current_student_id() {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    private static function table($name) {
        global $wpdb;
        return $wpdb->prefix . 'inssetsup_' . $name;
    }

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

    public static function login_student($id) {
        $_SESSION[self::SESSION_KEY] = $id;
    }

    public static function logout_student() {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
