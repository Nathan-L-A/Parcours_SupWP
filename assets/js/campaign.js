(function ($) {
    'use strict';

    // Données injectées via wp_localize_script (InssetsupCampaign)
    // { ajax_url, nonce, formations: [{id, name}, ...] }
    var formations = (typeof InssetsupCampaign !== 'undefined') ? InssetsupCampaign.formations : [];

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────

    /**
     * Reconstruit les <option> d'un <select> en excluant certains ids.
     * Conserve la valeur sélectionnée si elle n'est pas exclue.
     */
    function rebuildSelect($select, excludeIds) {
        var current = $select.val();

        $select.find('option:not(:first)').remove();

        $.each(formations, function (_, f) {
            if (excludeIds.indexOf(f.id) !== -1) return;
            $select.append($('<option>').val(f.id).text(f.name));
        });

        // Rétablit la valeur si encore disponible, sinon revient au placeholder
        $select.val(current);
    }

    /**
     * Gère la cascade des trois <select> :
     *  - Choix 2 est activé uniquement si Choix 1 est sélectionné.
     *  - Choix 3 est activé uniquement si Choix 2 est sélectionné.
     *  - Les formations déjà choisies sont exclues des autres listes.
     */
    function updateSelects() {
        var $s1 = $('#is-choice-1');
        var $s2 = $('#is-choice-2');
        var $s3 = $('#is-choice-3');
        var v1  = $s1.val();

        // ── Select 2 ──────────────────────────
        if (v1) {
            $s2.prop('disabled', false);
            rebuildSelect($s2, [v1]);
        } else {
            // Réinitialise et désactive les deux suivants
            $s2.val('').prop('disabled', true);
            $s3.val('').prop('disabled', true);
            return;
        }

        var v2 = $s2.val();

        // ── Select 3 ──────────────────────────
        if (v2) {
            $s3.prop('disabled', false);
            rebuildSelect($s3, [v1, v2]);
        } else {
            $s3.val('').prop('disabled', true);
        }
    }

    /**
     * Affiche un message de feedback.
     */
    function showMessage($el, message, type) {
        $el.text(message)
           .removeClass('is-message--success is-message--error')
           .addClass('is-message--' + type);
    }

    // ─────────────────────────────────────────
    // Init
    // ─────────────────────────────────────────

    $(function () {
        var $form   = $('#is-campaign-form');
        var $recap  = $('#is-recap');
        var $msg    = $('#is-campaign-message');
        var $submit = $('#is-submit-choices');

        if (!$form.length) return; // Shortcode absent de la page

        // ── Pré-remplissage (choix existants) ─
        // Si le choix 1 est déjà sélectionné (rendu côté PHP), on active la cascade.
        if ($('#is-choice-1').val()) {
            // Active temporairement select 2 et 3 pour pouvoir lire leurs valeurs
            $('#is-choice-2, #is-choice-3').prop('disabled', false);
            updateSelects();
        }

        // ── Changement de sélection ───────────
        $(document).on('change', '#is-choice-1, #is-choice-2', function () {
            updateSelects();
        });

        // ── Bouton "Modifier mes choix" ───────
        $('#is-edit-btn').on('click', function () {
            $recap.addClass('is-hidden');
            $form.removeClass('is-hidden');
            // Réactive la cascade pour l'édition
            if ($('#is-choice-1').val()) {
                $('#is-choice-2, #is-choice-3').prop('disabled', false);
                updateSelects();
            }
        });

        // ── Soumission du formulaire ──────────
        $form.on('submit', function (e) {
            e.preventDefault();

            var c1 = $('#is-choice-1').val();
            var c2 = $('#is-choice-2').val();
            var c3 = $('#is-choice-3').val();

            // Validation côté client (doublons, champs vides)
            if (!c1 || !c2 || !c3) {
                showMessage($msg, 'Veuillez sélectionner vos 3 choix.', 'error');
                return;
            }

            if (c1 === c2 || c1 === c3 || c2 === c3) {
                showMessage($msg, 'Vous ne pouvez pas sélectionner la même formation deux fois.', 'error');
                return;
            }

            $submit.prop('disabled', true);
            $msg.removeClass('is-message--success is-message--error').text('');

            $.post(
                InssetsupCampaign.ajax_url,
                {
                    action:   'inssetsup_save_choices',
                    nonce:    InssetsupCampaign.nonce,
                    choice_1: c1,
                    choice_2: c2,
                    choice_3: c3,
                },
                function (res) {
                    $submit.prop('disabled', false);

                    if (!res.success) {
                        showMessage($msg, res.data.message, 'error');
                        return;
                    }

                    // Mise à jour du récapitulatif et bascule vers l'affichage recap
                    var $list = $recap.find('.is-recap-list');
                    $list.empty();
                    $.each(res.data.recap, function (_, name) {
                        $list.append($('<li>').text(name));
                    });

                    $form.addClass('is-hidden');
                    $recap.removeClass('is-hidden');
                    $msg.removeClass('is-message--success is-message--error').text('');
                }
            );
        });
    });

}(jQuery));
