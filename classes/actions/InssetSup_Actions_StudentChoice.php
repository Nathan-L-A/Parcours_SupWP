<?php

// Les étudiants ne sont pas des utilisateurs WordPress : on déclare les deux hooks.
add_action('wp_ajax_inssetsup_save_choices',        array('InssetSup_Actions_StudentChoice', 'save_choices'));
add_action('wp_ajax_nopriv_inssetsup_save_choices',  array('InssetSup_Actions_StudentChoice', 'save_choices'));

class InssetSup_Actions_StudentChoice {

    public static function save_choices() {
        check_ajax_referer('inssetsup_campaign_nonce', 'nonce');

        if (!InssetSup_Helper_Auth::is_student_logged_in())
            wp_send_json_error(array('message' => 'Vous devez être connecté pour enregistrer vos choix.'));

        $c1 = isset($_POST['choice_1']) ? sanitize_text_field(wp_unslash($_POST['choice_1'])) : '';
        $c2 = isset($_POST['choice_2']) ? sanitize_text_field(wp_unslash($_POST['choice_2'])) : '';
        $c3 = isset($_POST['choice_3']) ? sanitize_text_field(wp_unslash($_POST['choice_3'])) : '';

        if (!$c1 || !$c2 || !$c3)
            wp_send_json_error(array('message' => 'Veuillez sélectionner vos 3 choix.'));

        if ($c1 === $c2 || $c1 === $c3 || $c2 === $c3)
            wp_send_json_error(array('message' => 'Vous ne pouvez pas sélectionner la même formation deux fois.'));

        $campaign = InssetSup_Crud_StudentChoice::get_active_campaign();
        if (!$campaign)
            wp_send_json_error(array('message' => 'Aucune campagne active.'));

        // Vérification que les choix appartiennent bien à la campagne active
        $formations = InssetSup_Crud_StudentChoice::get_campaign_formations($campaign->id_campaign);
        $valid_ids  = wp_list_pluck($formations, 'id_choice');

        foreach (array($c1, $c2, $c3) as $cid) {
            if (!in_array($cid, $valid_ids, true))
                wp_send_json_error(array('message' => 'Un choix soumis est invalide.'));
        }

        $student_id = InssetSup_Helper_Auth::get_current_student_id();
        $stc_id     = InssetSup_Crud_StudentChoice::get_or_create_stc($student_id, $campaign->id_campaign);

        if (!$stc_id)
            wp_send_json_error(array('message' => 'Erreur lors de l\'association à la campagne.'));

        $ok = InssetSup_Crud_StudentChoice::save_choices($stc_id, array($c1, $c2, $c3));

        if (!$ok)
            wp_send_json_error(array('message' => 'Erreur lors de la sauvegarde des choix.'));

        // Récupération du récap pour la réponse
        $saved = InssetSup_Crud_StudentChoice::get_student_choices($stc_id);
        $recap = array();
        foreach ($saved as $row)
            $recap[] = $row->name_choice;

        wp_send_json_success(array(
            'message' => 'Vos choix ont été enregistrés avec succès.',
            'recap'   => $recap,
        ));
    }
}
