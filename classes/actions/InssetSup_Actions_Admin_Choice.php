<?php

add_action('wp_ajax_inssetsup_choice_save',   array('InssetSup_Actions_Admin_Choice', 'save'));
add_action('wp_ajax_inssetsup_choice_delete', array('InssetSup_Actions_Admin_Choice', 'delete'));
add_action('wp_ajax_inssetsup_choice_get',    array('InssetSup_Actions_Admin_Choice', 'get'));

class InssetSup_Actions_Admin_Choice {

    public static function save() {
        check_ajax_referer('inssetsup_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error(array('message' => 'Accès refusé.'));

        $id   = sanitize_text_field(wp_unslash($_POST['id_choice'] ?? ''));
        $data = array(
            'name_choice' => sanitize_text_field(wp_unslash($_POST['name_choice'] ?? '')),
            'desc_choice' => sanitize_textarea_field(wp_unslash($_POST['desc_choice'] ?? '')),
            'isactivated' => empty($_POST['isactivated']) ? 0 : 1,
        );

        if (empty($data['name_choice']))
            wp_send_json_error(array('message' => 'Le nom de la formation est requis.'));

        if ($id)
            InssetSup_Crud_Choice::update($id, $data);
        else
            InssetSup_Crud_Choice::create($data);

        wp_send_json_success();
    }

    public static function delete() {
        check_ajax_referer('inssetsup_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error(array('message' => 'Accès refusé.'));

        $id = sanitize_text_field(wp_unslash($_POST['id_choice'] ?? ''));
        if (!$id)
            wp_send_json_error(array('message' => 'ID manquant.'));

        InssetSup_Crud_Choice::delete($id);
        wp_send_json_success();
    }

    public static function get() {
        check_ajax_referer('inssetsup_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error(array('message' => 'Accès refusé.'));

        $id     = sanitize_text_field(wp_unslash($_POST['id_choice'] ?? ''));
        $choice = InssetSup_Crud_Choice::get_by_id($id);

        if (!$choice)
            wp_send_json_error(array('message' => 'Formation introuvable.'));

        wp_send_json_success($choice);
    }
}
