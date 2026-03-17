<?php

class InssetSup_Crud_Choice {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'inssetsup_choice';
    }

    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results(
            'SELECT * FROM `' . self::table() . '` ORDER BY `name_choice` ASC'
        );
    }

    public static function get_active() {
        global $wpdb;
        return $wpdb->get_results(
            'SELECT * FROM `' . self::table() . '` WHERE `isactivated` = 1 AND `isarchived` = 0 ORDER BY `name_choice` ASC'
        );
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM `' . self::table() . '` WHERE `id_choice` = %s', $id)
        );
    }

    public static function create($data) {
        global $wpdb;
        $now = current_time('mysql');
        return $wpdb->insert(
            self::table(),
            array(
                'id_choice'   => uniqid('cho_', true),
                'name_choice' => $data['name_choice'],
                'desc_choice' => $data['desc_choice'],
                'isactivated' => 1,
                'isarchived'  => 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ),
            array('%s', '%s', '%s', '%d', '%d', '%s', '%s')
        );
    }

    public static function update($id, $data) {
        global $wpdb;
        return $wpdb->update(
            self::table(),
            array(
                'name_choice' => $data['name_choice'],
                'desc_choice' => $data['desc_choice'],
                'isactivated' => (int) $data['isactivated'],
                'updated_at'  => current_time('mysql'),
            ),
            array('id_choice' => $id),
            array('%s', '%s', '%d', '%s'),
            array('%s')
        );
    }

    public static function delete($id) {
        global $wpdb;
        return $wpdb->delete(self::table(), array('id_choice' => $id), array('%s'));
    }
}
