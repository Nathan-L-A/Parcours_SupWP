<?php

/**
 * CRUD des campagnes d'orientation.
 *
 * Une campagne regroupe un ensemble de formations (choices) parmi lesquelles
 * les étudiants formulent leurs vœux. Elle peut être activée/désactivée
 * et possède des dates de début / fin optionnelles.
 *
 * Tables utilisées :
 *   - wp_inssetsup_campaign              : données de la campagne
 *   - wp_inssetsup_asso_campaign_choice  : liaison N-N campagne ↔ formations
 *   - wp_inssetsup_student_to_campaign   : liaison étudiant ↔ campagne (pour
 *                                          vérifier si une suppression est possible)
 */

class InssetSup_Crud_Campaign {

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'inssetsup_campaign';
    }

    private static function table_asso() {
        global $wpdb;
        return $wpdb->prefix . 'inssetsup_asso_campaign_choice';
    }

    private static function table_stc() {
        global $wpdb;
        return $wpdb->prefix . 'inssetsup_student_to_campaign';
    }

    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results(
            'SELECT * FROM `' . self::table() . '` ORDER BY `created_at` DESC'
        );
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM `' . self::table() . '` WHERE `id_campaign` = %s', $id)
        );
    }

    public static function get_choices_for_campaign($campaign_id) {
        global $wpdb;
        $t_asso   = self::table_asso();
        $t_choice = $wpdb->prefix . 'inssetsup_choice';
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.* FROM `$t_choice` c
                 INNER JOIN `$t_asso` a ON a.id_choice = c.id_choice
                 WHERE a.id_campaign = %s",
                $campaign_id
            )
        );
    }

    public static function count_students($campaign_id) {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(*) FROM `' . self::table_stc() . '` WHERE `id_campaign` = %s',
                $campaign_id
            )
        );
    }

    public static function create($data) {
        global $wpdb;
        $now = current_time('mysql');
        $id  = uniqid('cam_', true);

        $res = $wpdb->insert(
            self::table(),
            array(
                'id_campaign'   => $id,
                'name_campaign' => $data['name_campaign'],
                'desc_campaign' => $data['desc_campaign'],
                'startdate'     => $data['startdate'] ?: null,
                'end_date'      => $data['end_date'] ?: null,
                'isactivated'   => (int) $data['isactivated'],
                'created_at'    => $now,
                'updated_at'    => $now,
            ),
            array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );

        if ($res && !empty($data['choices']))
            self::sync_choices($id, $data['choices']);

        return $res ? $id : false;
    }

    public static function update($id, $data) {
        global $wpdb;
        $wpdb->update(
            self::table(),
            array(
                'name_campaign' => $data['name_campaign'],
                'desc_campaign' => $data['desc_campaign'],
                'startdate'     => $data['startdate'] ?: null,
                'end_date'      => $data['end_date'] ?: null,
                'isactivated'   => (int) $data['isactivated'],
                'updated_at'    => current_time('mysql'),
            ),
            array('id_campaign' => $id),
            array('%s', '%s', '%s', '%s', '%d', '%s'),
            array('%s')
        );

        self::sync_choices($id, $data['choices'] ?? array());
    }

    public static function delete($id) {
        global $wpdb;

        if (self::count_students($id) > 0)
            return new WP_Error(
                'has_students',
                'Impossible de supprimer : des étudiants ont déjà formulé des choix pour cette campagne.'
            );

        $wpdb->delete(self::table_asso(), array('id_campaign' => $id), array('%s'));
        return $wpdb->delete(self::table(), array('id_campaign' => $id), array('%s'));
    }

    /**
     * Synchronise la table d'association campaign ↔ choices.
     * Supprime toutes les liaisons existantes puis reinsère les nouvelles.
     *
     * @param string   $campaign_id  Identifiant de la campagne.
     * @param string[] $choice_ids   IDs des formations à associer.
     */
    private static function sync_choices($campaign_id, $choice_ids) {
        global $wpdb;
        $t = self::table_asso();
        $wpdb->delete($t, array('id_campaign' => $campaign_id), array('%s'));
        foreach ((array) $choice_ids as $cid) {
            if (empty($cid))
                continue;
            $wpdb->insert(
                $t,
                array('id_campaign' => $campaign_id, 'id_choice' => $cid),
                array('%s', '%s')
            );
        }
    }
}
