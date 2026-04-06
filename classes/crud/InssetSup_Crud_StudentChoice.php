<?php

/**
 * CRUD des vœux étudiants.
 *
 * Centralise toutes les requêtes liées aux choix des étudiants :
 *   - récupération des campagnes éligibles
 *   - liaison étudiant ↔ campagne (student_to_campaign)
 *   - lecture et sauvegarde des 3 vœux ordonnés
 *
 * Tables utilisées :
 *   - wp_inssetsup_campaign              : données des campagnes
 *   - wp_inssetsup_asso_campaign_choice  : formations associées à une campagne
 *   - wp_inssetsup_choice                : détail des formations
 *   - wp_inssetsup_student_to_campaign   : lien étudiant ↔ campagne
 *   - wp_inssetsup_student_choice        : vœux ordonnés de l'étudiant
 */

class InssetSup_Crud_StudentChoice {

    private static function table($name) {
        global $wpdb;
        return $wpdb->prefix . 'inssetsup_' . $name;
    }

    // ─────────────────────────────────────────
    // Campagnes actives
    // ─────────────────────────────────────────

    public static function get_active_campaign() {
        global $wpdb;
        return $wpdb->get_row(
            "SELECT * FROM `" . self::table('campaign') . "`
             WHERE `isactivated` = 1
             ORDER BY `created_at` DESC
             LIMIT 1"
        );
    }

    /**
     * Retourne les campagnes actives ayant au moins $min formations actives associées.
     */
    public static function get_active_campaigns_with_enough_formations($min = 3) {
        global $wpdb;
        $t_campaign = self::table('campaign');
        $t_asso     = self::table('asso_campaign_choice');
        $t_choice   = self::table('choice');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT camp.*
                 FROM `$t_campaign` camp
                 WHERE camp.isactivated = 1
                 AND (
                     SELECT COUNT(*)
                     FROM `$t_asso` a
                     INNER JOIN `$t_choice` ch ON ch.id_choice = a.id_choice AND ch.isactivated = 1
                     WHERE a.id_campaign = camp.id_campaign
                 ) >= %d
                 ORDER BY camp.created_at DESC",
                $min
            )
        );
    }

    // ─────────────────────────────────────────
    // Formations d'une campagne
    // ─────────────────────────────────────────

    public static function get_campaign_formations($campaign_id) {
        global $wpdb;
        $t_asso   = self::table('asso_campaign_choice');
        $t_choice = self::table('choice');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.* FROM `$t_choice` c
                 INNER JOIN `$t_asso` a ON a.id_choice = c.id_choice
                 WHERE a.id_campaign = %s AND c.isactivated = 1
                 ORDER BY c.name_choice ASC",
                $campaign_id
            )
        );
    }

    // ─────────────────────────────────────────
    // Lien étudiant ↔ campagne
    // ─────────────────────────────────────────

    /**
     * Retourne l'id_student_to_campaign existant ou en crée un nouveau.
     *
     * @return string|false  L'identifiant, ou false en cas d'erreur.
     */
    public static function get_or_create_stc($student_id, $campaign_id) {
        global $wpdb;
        $t = self::table('student_to_campaign');

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `id_student_to_campaign` FROM `$t`
                 WHERE `id_student` = %s AND `id_campaign` = %s",
                $student_id,
                $campaign_id
            )
        );

        if ($row)
            return $row->id_student_to_campaign;

        $id = uniqid('stc_', true);

        $wpdb->insert(
            $t,
            array(
                'id_student_to_campaign' => $id,
                'id_student'             => $student_id,
                'id_campaign'            => $campaign_id,
                'status_candidate'       => 'pending',
                'date_add'               => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );

        return empty($wpdb->last_error) ? $id : false;
    }

    /**
     * Retourne l'id_student_to_campaign existant sans en créer un nouveau.
     * Utile pour lire les choix existants sans les initialiser.
     */
    public static function get_student_stc_id($student_id, $campaign_id) {
        global $wpdb;
        $t = self::table('student_to_campaign');

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `id_student_to_campaign` FROM `$t`
                 WHERE `id_student` = %s AND `id_campaign` = %s",
                $student_id,
                $campaign_id
            )
        );

        return $row ? $row->id_student_to_campaign : null;
    }

    // ─────────────────────────────────────────
    // Choix de l'étudiant
    // ─────────────────────────────────────────

    /**
     * Retourne les 3 choix ordonnés de l'étudiant avec le nom de la formation.
     */
    public static function get_student_choices($stc_id) {
        global $wpdb;
        $t_sc = self::table('student_choice');
        $t_ch = self::table('choice');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT sc.id_student_choice, sc.id_choice, sc.choice_order, c.name_choice
                 FROM `$t_sc` sc
                 LEFT JOIN `$t_ch` c ON c.id_choice = sc.id_choice
                 WHERE sc.id_student_to_campaign = %s
                 ORDER BY sc.choice_order ASC",
                $stc_id
            )
        );
    }

    /**
     * Enregistre (remplace) les 3 choix ordonnés.
     *
     * @param string $stc_id   id_student_to_campaign
     * @param array  $choices  [ choice_id_1, choice_id_2, choice_id_3 ] (index 0-based)
     * @return bool
     */
    public static function save_choices($stc_id, $choices) {
        global $wpdb;
        $t   = self::table('student_choice');
        $now = current_time('mysql');

        // Suppression des choix existants
        $wpdb->delete($t, array('id_student_to_campaign' => $stc_id), array('%s'));

        foreach ($choices as $index => $choice_id) {
            $res = $wpdb->insert(
                $t,
                array(
                    'id_student_choice'      => uniqid('sc_', true),
                    'id_student_to_campaign' => $stc_id,
                    'id_choice'              => $choice_id,
                    'choice_order'           => (int) $index + 1,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            if ($res === false)
                return false;
        }

        return true;
    }
}
