<?php

class InssetSup_Install_Index {

    public function setup() {

        global $wpdb;
        $p = $wpdb->prefix . 'inssetsup_';
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Création BDD si elle n'existe pas 
        dbDelta("CREATE TABLE IF NOT EXISTS `{$p}student` (
            `id_student` VARCHAR(50) NOT NULL,
            `lname_student` VARCHAR(50),
            `fname_student` VARCHAR(50),
            `email_student` VARCHAR(100),
            `password` VARCHAR(255),
            `created_at` DATETIME,
            `updated_at` DATETIME,
            `isactivated` TINYINT(1) DEFAULT 0,
            `isarchived` TINYINT(1) DEFAULT 0,
            PRIMARY KEY (`id_student`)
        ) ENGINE=InnoDB $charset_collate;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$p}campaign` (
            `id_campaign` VARCHAR(50) NOT NULL,
            `name_campaign` VARCHAR(50),
            `desc_campaign` VARCHAR(300),
            `startdate` DATETIME,
            `end_date` DATETIME,
            `isactivated` TINYINT(1) DEFAULT 0,
            `created_at` DATETIME,
            `updated_at` DATETIME,
            PRIMARY KEY (`id_campaign`)
        ) ENGINE=InnoDB $charset_collate;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$p}choice` (
            `id_choice` VARCHAR(50) NOT NULL,
            `id_campaign` VARCHAR(50),
            `name_choice` VARCHAR(125),
            `desc_choice` VARCHAR(300),
            `created_at` DATETIME,
            `updated_at` DATETIME,
            `isactivated` TINYINT(1) DEFAULT 0,
            `isarchived` TINYINT(1) DEFAULT 0,
            PRIMARY KEY (`id_choice`)
        ) ENGINE=InnoDB $charset_collate;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$p}student_choice` (
            `id_student_choice` VARCHAR(50) NOT NULL,
            `id_student_to_campaign` VARCHAR(50),
            `id_choice` VARCHAR(50),
            `choice_order` INT DEFAULT 0,
            `created_at` DATETIME,
            `updated_at` DATETIME,
            PRIMARY KEY (`id_student_choice`)
        ) ENGINE=InnoDB $charset_collate;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$p}student_to_campaign` (
            `id_student_to_campaign` VARCHAR(50) NOT NULL,
            `id_student` VARCHAR(50) NOT NULL,
            `id_campaign` VARCHAR(50) NOT NULL,
            `num_candidate` INT,
            `status_candidate` VARCHAR(50),
            `date_add` DATETIME,
            PRIMARY KEY (`id_student_to_campaign`),
            KEY `idx_stc_student` (`id_student`),
            KEY `idx_stc_campaign` (`id_campaign`)
        ) ENGINE=InnoDB $charset_collate;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$p}asso_campaign_choice` (
            `id_campaign` VARCHAR(50) NOT NULL,
            `id_choice` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id_campaign`, `id_choice`)
        ) ENGINE=InnoDB $charset_collate;");

        dbDelta("CREATE TABLE IF NOT EXISTS `{$p}asso_student_choice_choice` (
            `id_student_choice` VARCHAR(50) NOT NULL,
            `id_choice` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id_student_choice`, `id_choice`)
        ) ENGINE=InnoDB $charset_collate;");

        $this->setup_front_page();
        $this->setup_campaign_page();

    }

    private function setup_front_page() {

        // Vérifie si la page d'accueil existe déjà
        $existing = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_key'       => '_inssetsup_page',
            'meta_value'     => 'home',
        ));

        if (!empty($existing)) {
            $page_id = $existing[0]->ID;
        } else {
            $page_id = wp_insert_post(array(
                'post_title'   => 'Accueil',
                'post_content' => '[inssetsup_auth]',
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ));

            if (is_wp_error($page_id))
                return;

            update_post_meta($page_id, '_inssetsup_page', 'home');
        }

        // Définit cette page comme page d'accueil statique
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_id);

    }

    private function setup_campaign_page() {
        if (get_option('inssetsup_campaign_page_id'))
            return;

        $page_id = wp_insert_post(array(
            'post_title'   => 'Campagne',
            'post_name'    => 'campagne',
            'post_content' => '[inssetsup_campagne]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ));

        if (!is_wp_error($page_id) && $page_id) {
            update_post_meta($page_id, '_inssetsup_page', 'campaign');
            update_option('inssetsup_campaign_page_id', $page_id);
        }
    }

}
