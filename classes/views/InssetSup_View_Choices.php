<?php

class InssetSup_View_Choices {

    public function render() {
        $choices = InssetSup_Crud_Choice::get_all();
        ?>
        <div class="wrap is-admin-wrap">
            <h1 class="wp-heading-inline">Formations / Spécialités</h1>
            <button class="page-title-action is-btn-add" id="is-choice-add-btn">Ajouter une formation</button>
            <hr class="wp-header-end">

            <?php if (empty($choices)) : ?>
                <div class="notice notice-info"><p>Aucune formation enregistrée pour l'instant.</p></div>
            <?php else : ?>
            <table class="wp-list-table widefat fixed striped is-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th style="width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="is-choices-tbody">
                <?php foreach ($choices as $c) : ?>
                    <tr data-id="<?php echo esc_attr($c->id_choice); ?>">
                        <td><strong><?php echo esc_html($c->name_choice); ?></strong></td>
                        <td><?php echo esc_html($c->desc_choice); ?></td>
                        <td>
                            <?php if ($c->isactivated) : ?>
                                <span class="is-badge is-badge--active">Actif</span>
                            <?php else : ?>
                                <span class="is-badge is-badge--inactive">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="button is-btn-edit" data-id="<?php echo esc_attr($c->id_choice); ?>">Modifier</button>
                            <button class="button button-link-delete is-btn-delete" data-id="<?php echo esc_attr($c->id_choice); ?>">Supprimer</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Modal formation -->
        <div class="is-modal-overlay" id="is-choice-modal" hidden>
            <div class="is-modal">
                <div class="is-modal__header">
                    <h2 class="is-modal__title" id="is-choice-modal-title">Ajouter une formation</h2>
                    <button class="is-modal__close" aria-label="Fermer">&times;</button>
                </div>
                <form id="is-choice-form">
                    <input type="hidden" name="id_choice" id="is-choice-id" value="">
                    <div class="is-form-group">
                        <label for="is-choice-name">Nom <span>*</span></label>
                        <input type="text" id="is-choice-name" name="name_choice" required>
                    </div>
                    <div class="is-form-group">
                        <label for="is-choice-desc">Description</label>
                        <textarea id="is-choice-desc" name="desc_choice" rows="4"></textarea>
                    </div>
                    <div class="is-form-group is-form-group--inline">
                        <label>
                            <input type="checkbox" name="isactivated" id="is-choice-active" value="1" checked>
                            Formation active
                        </label>
                    </div>
                    <div class="is-modal__footer">
                        <div class="is-feedback" id="is-choice-feedback"></div>
                        <button type="button" class="button is-modal__close">Annuler</button>
                        <button type="submit" class="button button-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
