<?php

class InssetSup_Shortcode_Campaign {

    public static function render() {

        if (!InssetSup_Helper_Auth::is_student_logged_in()) {
            wp_redirect(home_url('/'));
            exit;
        }

        $campaign_id = isset($_GET['campaign_id'])
            ? sanitize_text_field(wp_unslash($_GET['campaign_id']))
            : '';

        if ($campaign_id)
            return self::render_form($campaign_id);

        return self::render_list();
    }

    // ─────────────────────────────────────────
    // Barre supérieure (nom étudiant + déconnexion)
    // ─────────────────────────────────────────

    private static function render_topbar() {
        $student = InssetSup_Helper_Auth::get_current_student();
        $name    = $student
            ? esc_html($student->fname_student . ' ' . $student->lname_student)
            : '';
        ob_start();
        ?>
        <div class="is-card-topbar">
            <?php if ($name): ?>
                <span class="is-student-name">Bonjour, <?php echo $name; ?></span>
            <?php endif; ?>
            <button id="is-logout-btn" class="is-btn is-btn--logout">Déconnexion</button>
        </div>
        <?php
        return ob_get_clean();
    }

    // ─────────────────────────────────────────
    // Liste des campagnes
    // ─────────────────────────────────────────

    private static function render_list() {
        $campaigns = InssetSup_Crud_StudentChoice::get_active_campaigns_with_enough_formations(3);

        ob_start();

        if (empty($campaigns)) {
            ?>
            <div class="is-campaign-wrap">
                <div class="is-campaign-card">
                    <?php echo self::render_topbar(); ?>
                    <div class="is-no-campaign">
                        <p>Aucune campagne disponible pour le moment. Revenez plus tard.</p>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        ?>
        <div class="is-campaign-wrap">
            <div class="is-campaign-card">
                <?php echo self::render_topbar(); ?>
                <h2 class="is-list-title">Campagnes disponibles</h2>
                <ul class="is-campaign-items">
                    <?php foreach ($campaigns as $camp): ?>
                    <li>
                        <a
                            class="is-campaign-item"
                            href="<?php echo esc_url(add_query_arg('campaign_id', $camp->id_campaign, get_permalink())); ?>"
                        >
                            <div class="is-campaign-item__info">
                                <span class="is-campaign-item__name"><?php echo esc_html($camp->name_campaign); ?></span>
                                <?php if (!empty($camp->desc_campaign)): ?>
                                <span class="is-campaign-item__desc"><?php echo esc_html($camp->desc_campaign); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="is-campaign-item__arrow">→</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ─────────────────────────────────────────
    // Formulaire d'une campagne
    // ─────────────────────────────────────────

    private static function render_form($campaign_id) {

        // Validation : la campagne doit être active et avoir ≥ 3 formations
        $campaigns = InssetSup_Crud_StudentChoice::get_active_campaigns_with_enough_formations(3);
        $campaign  = null;
        foreach ($campaigns as $c) {
            if ($c->id_campaign === $campaign_id) {
                $campaign = $c;
                break;
            }
        }

        if (!$campaign) {
            wp_redirect(get_permalink());
            exit;
        }

        $student_id  = InssetSup_Helper_Auth::get_current_student_id();
        $formations  = InssetSup_Crud_StudentChoice::get_campaign_formations($campaign->id_campaign);
        $stc_id      = InssetSup_Crud_StudentChoice::get_student_stc_id($student_id, $campaign->id_campaign);
        $existing    = $stc_id ? InssetSup_Crud_StudentChoice::get_student_choices($stc_id) : array();
        $has_choices = count($existing) === 3;

        $defaults = array();
        foreach ($existing as $ch)
            $defaults[(int) $ch->choice_order] = $ch->id_choice;

        $labels = array(
            1 => '1<sup>er</sup> choix',
            2 => '2<sup>ème</sup> choix',
            3 => '3<sup>ème</sup> choix',
        );

        $back_url = remove_query_arg('campaign_id');

        ob_start();
        ?>
        <div class="is-campaign-wrap">
            <div class="is-campaign-card">

                <?php echo self::render_topbar(); ?>
                <a href="<?php echo esc_url($back_url); ?>" class="is-btn is-btn--back">← Retour aux campagnes</a>

                <div class="is-campaign-header">
                    <h2><?php echo esc_html($campaign->name_campaign); ?></h2>
                    <?php if (!empty($campaign->desc_campaign)): ?>
                        <p class="is-campaign-desc"><?php echo esc_html($campaign->desc_campaign); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Récapitulatif -->
                <div id="is-recap" class="is-recap<?php echo $has_choices ? '' : ' is-hidden'; ?>">
                    <h3>Vos choix enregistrés</h3>
                    <ol class="is-recap-list">
                        <?php foreach ($existing as $ch): ?>
                            <li><?php echo esc_html($ch->name_choice); ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <button id="is-edit-btn" class="is-btn is-btn--outline">Modifier mes choix</button>
                </div>

                <!-- Formulaire -->
                <form id="is-campaign-form" class="is-campaign-form<?php echo $has_choices ? ' is-hidden' : ''; ?>" novalidate>
                    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign->id_campaign); ?>">

                    <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="is-select-group">
                        <label for="is-choice-<?php echo $i; ?>"><?php echo $labels[$i]; ?></label>
                        <select
                            id="is-choice-<?php echo $i; ?>"
                            name="choice_<?php echo $i; ?>"
                            class="is-choice-select"
                            data-order="<?php echo $i; ?>"
                            <?php echo $i > 1 ? 'disabled' : ''; ?>
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
