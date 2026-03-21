<?php

class InssetSup_Shortcode_Campaign {

    public static function render() {

        // Redirection si non connecté
        if (!InssetSup_Helper_Auth::is_student_logged_in()) {
            wp_redirect(home_url('/'));
            exit;
        }

        $campaign = InssetSup_Crud_StudentChoice::get_active_campaign();

        // Pas de campagne active
        if (!$campaign) {
            ob_start();
            ?>
            <div class="is-campaign-wrap">
                <div class="is-campaign-card">
                    <div class="is-no-campaign">
                        <p>Aucune campagne active pour le moment. Revenez plus tard.</p>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        $student_id = InssetSup_Helper_Auth::get_current_student_id();
        $stc_id     = InssetSup_Crud_StudentChoice::get_or_create_stc($student_id, $campaign->id_campaign);

        // Erreur de liaison étudiant/campagne
        if (!$stc_id) {
            ob_start();
            ?>
            <div class="is-campaign-wrap">
                <div class="is-campaign-card">
                    <div class="is-no-campaign">
                        <p>Une erreur est survenue. Veuillez vous déconnecter et réessayer.</p>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        $formations  = InssetSup_Crud_StudentChoice::get_campaign_formations($campaign->id_campaign);
        $existing    = InssetSup_Crud_StudentChoice::get_student_choices($stc_id);
        $has_choices = count($existing) === 3;

        // Valeurs pré-sélectionnées (indexées par order 1-3)
        $defaults = array();
        foreach ($existing as $ch)
            $defaults[(int) $ch->choice_order] = $ch->id_choice;

        $labels = array(
            1 => '1<sup>er</sup> choix',
            2 => '2<sup>ème</sup> choix',
            3 => '3<sup>ème</sup> choix',
        );

        ob_start();
        ?>
        <div class="is-campaign-wrap">
            <div class="is-campaign-card">

                <!-- En-tête campagne -->
                <div class="is-campaign-header">
                    <h2><?php echo esc_html($campaign->name_campaign); ?></h2>
                    <?php if (!empty($campaign->desc_campaign)): ?>
                        <p class="is-campaign-desc"><?php echo esc_html($campaign->desc_campaign); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Récapitulatif (affiché si choix déjà enregistrés) -->
                <div id="is-recap" class="is-recap<?php echo $has_choices ? '' : ' is-hidden'; ?>">
                    <h3>Vos choix enregistrés</h3>
                    <ol class="is-recap-list">
                        <?php foreach ($existing as $ch): ?>
                            <li><?php echo esc_html($ch->name_choice); ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <button id="is-edit-btn" class="is-btn is-btn--outline">Modifier mes choix</button>
                </div>

                <!-- Formulaire de sélection -->
                <form id="is-campaign-form" class="is-campaign-form<?php echo $has_choices ? ' is-hidden' : ''; ?>" novalidate>

                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="is-select-group">
                        <label for="is-choice-<?php echo $i; ?>"><?php echo $labels[$i]; ?></label>
                        <select
                            id="is-choice-<?php echo $i; ?>"
                            name="choice_<?php echo $i; ?>"
                            class="is-choice-select"
                            data-order="<?php echo $i; ?>"
                            <?php echo ($i > 1) ? 'disabled' : ''; ?>
                        >
                            <option value="">— Sélectionner une formation —</option>
                            <?php foreach ($formations as $f): ?>
                                <option
                                    value="<?php echo esc_attr($f->id_choice); ?>"
                                    <?php selected($defaults[$i] ?? '', $f->id_choice); ?>
                                >
                                    <?php echo esc_html($f->name_choice); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endfor; ?>

                    <div class="is-form-actions">
                        <button type="submit" id="is-submit-choices" class="is-btn is-btn--primary">
                            Valider mes choix
                        </button>
                    </div>
                    <div id="is-campaign-message" class="is-message"></div>
                </form>

            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
