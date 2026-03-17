<?php

class InssetSup_View_Campaigns {

    public function render() {
        $campaigns    = InssetSup_Crud_Campaign::get_all();
        $all_choices  = InssetSup_Crud_Choice::get_active();
        ?>
        <div class="wrap is-admin-wrap">
            <h1 class="wp-heading-inline">Campagnes</h1>
            <button class="page-title-action is-btn-add" id="is-campaign-add-btn">Ajouter une campagne</button>
            <hr class="wp-header-end">

            <?php if (empty($campaigns)) : ?>
                <div class="notice notice-info"><p>Aucune campagne enregistrée pour l'instant.</p></div>
            <?php else : ?>
            <table class="wp-list-table widefat fixed striped is-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Étudiants</th>
                        <th>Statut</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="is-campaigns-tbody">
                <?php foreach ($campaigns as $c) : ?>
                    <tr data-id="<?php echo esc_attr($c->id_campaign); ?>">
                        <td><strong><?php echo esc_html($c->name_campaign); ?></strong></td>
                        <td><?php echo esc_html($c->desc_campaign); ?></td>
                        <td><?php echo $c->startdate ? esc_html(date_i18n('d/m/Y', strtotime($c->startdate))) : '—'; ?></td>
                        <td><?php echo $c->end_date  ? esc_html(date_i18n('d/m/Y', strtotime($c->end_date)))  : '—'; ?></td>
                        <td><?php echo esc_html(InssetSup_Crud_Campaign::count_students($c->id_campaign)); ?></td>
                        <td>
                            <?php if ($c->isactivated) : ?>
                                <span class="is-badge is-badge--active">Active</span>
                            <?php else : ?>
                                <span class="is-badge is-badge--inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="button is-btn-edit" data-id="<?php echo esc_attr($c->id_campaign); ?>">Modifier</button>
                            <button class="button button-link-delete is-btn-delete" data-id="<?php echo esc_attr($c->id_campaign); ?>">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Modal campagne -->
        <div class="is-modal-overlay" id="is-campaign-modal" hidden>
            <div class="is-modal is-modal--lg">
                <div class="is-modal__header">
                    <h2 class="is-modal__title" id="is-campaign-modal-title">Ajouter une campagne</h2>
                    <button class="is-modal__close" aria-label="Fermer">&times;</button>
                </div>
                <form id="is-campaign-form">
                    <input type="hidden" name="id_campaign" id="is-campaign-id" value="">
                    <div class="is-form-row">
                        <div class="is-form-group">
                            <label for="is-campaign-name">Nom <span>*</span></label>
                            <input type="text" id="is-campaign-name" name="name_campaign" required>
                        </div>
                        <div class="is-form-group">
                            <label>
                                <input type="checkbox" name="isactivated" id="is-campaign-active" value="1" checked>
                                Campagne active
                            </label>
                        </div>
                    </div>
                    <div class="is-form-group">
                        <label for="is-campaign-desc">Description</label>
                        <textarea id="is-campaign-desc" name="desc_campaign" rows="3"></textarea>
                    </div>
                    <div class="is-form-row">
                        <div class="is-form-group">
                            <label for="is-campaign-start">Date de début</label>
                            <input type="date" id="is-campaign-start" name="startdate">
                        </div>
                        <div class="is-form-group">
                            <label for="is-campaign-end">Date de fin</label>
                            <input type="date" id="is-campaign-end" name="end_date">
                        </div>
                    </div>
                    <div class="is-form-group">
                        <label>Formations associées</label>
                        <div class="is-choices-grid" id="is-campaign-choices">
                            <?php foreach ($all_choices as $ch) : ?>
                            <label class="is-choice-check">
                                <input type="checkbox" name="choices[]" value="<?php echo esc_attr($ch->id_choice); ?>">
                                <?php echo esc_html($ch->name_choice); ?>
                            </label>
                            <?php endforeach; ?>
                            <?php if (empty($all_choices)) : ?>
                                <em>Aucune formation active disponible.</em>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="is-modal__footer">
                        <div class="is-feedback" id="is-campaign-feedback"></div>
                        <button type="button" class="button is-modal__close">Annuler</button>
                        <button type="submit" class="button button-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
