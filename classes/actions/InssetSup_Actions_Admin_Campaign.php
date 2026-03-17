<?php

add_action('wp_ajax_inssetsup_campaign_save',   array('InssetSup_Actions_Admin_Campaign', 'save'));
add_action('wp_ajax_inssetsup_campaign_delete', array('InssetSup_Actions_Admin_Campaign', 'delete'));
add_action('wp_ajax_inssetsup_campaign_get',    array('InssetSup_Actions_Admin_Campaign', 'get'));

class InssetSup_Actions_Admin_Campaign {

    public static function save() {
        check_ajax_referer('inssetsup_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error(array('message' => 'Accès refusé.'));

        $id   = sanitize_text_field(wp_unslash($_POST['id_campaign'] ?? ''));
        $data = array(
            'name_campaign' => sanitize_text_field(wp_unslash($_POST['name_campaign'] ?? '')),
            'desc_campaign' => sanitize_textarea_field(wp_unslash($_POST['desc_campaign'] ?? '')),
            'startdate'     => sanitize_text_field(wp_unslash($_POST['startdate'] ?? '')),
            'end_date'      => sanitize_text_field(wp_unslash($_POST['end_date'] ?? '')),
            'isactivated'   => empty($_POST['isactivated']) ? 0 : 1,
            'choices'       => isset($_POST['choices'])
                ? array_map('sanitize_text_field', (array) wp_unslash($_POST['choices']))
                : array(),
        );

        if (empty($data['name_campaign']))
            wp_send_json_error(array('message' => 'Le nom de la campagne est requis.'));

        if ($id)
            InssetSup_Crud_Campaign::update($id, $data);
        else
            InssetSup_Crud_Campaign::create($data);

        wp_send_json_success();
    }

    public static function delete() {
        check_ajax_referer('inssetsup_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error(array('message' => 'Accès refusé.'));

        $id = sanitize_text_field(wp_unslash($_POST['id_campaign'] ?? ''));
        if (!$id)
            wp_send_json_error(array('message' => 'ID manquant.'));

        $result = InssetSup_Crud_Campaign::delete($id);

        if (is_wp_error($result))
            wp_send_json_error(array('message' => $result->get_error_message()));

        wp_send_json_success();
    }

    public static function get() {
        check_ajax_referer('inssetsup_admin_nonce', 'nonce');
        if (!current_user_can('manage_options'))
            wp_send_json_error(array('message' => 'Accès refusé.'));

        $id       = sanitize_text_field(wp_unslash($_POST['id_campaign'] ?? ''));
        $campaign = InssetSup_Crud_Campaign::get_by_id($id);

        if (!$campaign)
            wp_send_json_error(array('message' => 'Campagne introuvable.'));

        $choices    = InssetSup_Crud_Campaign::get_choices_for_campaign($id);
        $choice_ids = array_map(function ($c) { return $c->id_choice; }, $choices);

        wp_send_json_success(array(
            'campaign'   => $campaign,
            'choice_ids' => $choice_ids,
        ));
    }
}
