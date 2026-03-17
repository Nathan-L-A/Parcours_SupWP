(function ($) {
    'use strict';

    var ajaxUrl = InssetsupAdmin.ajax_url;
    var nonce   = InssetsupAdmin.nonce;

    // ── Helpers ───────────────────────────────────────────────────────

    function openModal($modal) {
        $modal.removeAttr('hidden');
    }

    function closeModal($modal) {
        $modal.attr('hidden', true);
    }

    function setFeedback($el, type, msg) {
        $el.removeClass('error success').addClass(type).text(msg);
    }

    function resetForm($form) {
        $form[0].reset();
        $form.find('.is-feedback').removeClass('error success').text('');
    }

    // ── Close modals ──────────────────────────────────────────────────

    $(document).on('click', '.is-modal__close', function () {
        closeModal($(this).closest('.is-modal-overlay'));
    });

    $(document).on('click', '.is-modal-overlay', function (e) {
        if ($(e.target).hasClass('is-modal-overlay'))
            closeModal($(this));
    });

    // ══════════════════════════════════════════════════════════════════
    //  FORMATIONS (choices)
    // ══════════════════════════════════════════════════════════════════

    var $choiceModal = $('#is-choice-modal');

    // Open modal — Add
    $('#is-choice-add-btn').on('click', function () {
        resetForm($('#is-choice-form'));
        $('#is-choice-modal-title').text('Ajouter une formation');
        $('#is-choice-id').val('');
        openModal($choiceModal);
    });

    // Open modal — Edit
    $(document).on('click', '.is-btn-edit[data-id]', function () {
        var id = $(this).data('id');

        // Distinguish choice vs campaign by closest table
        if ($(this).closest('#is-choices-tbody').length) {
            resetForm($('#is-choice-form'));
            $('#is-choice-modal-title').text('Modifier la formation');

            $.post(ajaxUrl, { action: 'inssetsup_choice_get', nonce: nonce, id_choice: id })
                .done(function (res) {
                    if (!res.success) return;
                    var d = res.data;
                    $('#is-choice-id').val(d.id_choice);
                    $('#is-choice-name').val(d.name_choice);
                    $('#is-choice-desc').val(d.desc_choice);
                    $('#is-choice-active').prop('checked', d.isactivated == 1);
                    openModal($choiceModal);
                });
        }
    });

    // Submit — Choice form
    $('#is-choice-form').on('submit', function (e) {
        e.preventDefault();
        var $form    = $(this);
        var $btn     = $form.find('[type="submit"]');
        var $fbk     = $('#is-choice-feedback');
        var formData = $form.serializeArray();
        var data     = { action: 'inssetsup_choice_save', nonce: nonce };

        // serializeArray skips unchecked checkboxes — handle manually
        data.isactivated = $form.find('[name="isactivated"]').is(':checked') ? 1 : 0;
        $.each(formData, function (_, f) { data[f.name] = f.value; });

        $btn.prop('disabled', true);

        $.post(ajaxUrl, data)
            .done(function (res) {
                if (res.success) {
                    closeModal($choiceModal);
                    location.reload();
                } else {
                    setFeedback($fbk, 'error', res.data.message);
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                setFeedback($fbk, 'error', 'Erreur réseau.');
                $btn.prop('disabled', false);
            });
    });

    // Delete — Choice
    $(document).on('click', '#is-choices-tbody .is-btn-delete', function () {
        var id   = $(this).data('id');
        var name = $(this).closest('tr').find('strong').text();

        if (!window.confirm('Supprimer la formation « ' + name + ' » ?'))
            return;

        $.post(ajaxUrl, { action: 'inssetsup_choice_delete', nonce: nonce, id_choice: id })
            .done(function (res) {
                if (res.success)
                    location.reload();
                else
                    alert(res.data.message);
            });
    });

    // ══════════════════════════════════════════════════════════════════
    //  CAMPAGNES
    // ══════════════════════════════════════════════════════════════════

    var $campaignModal = $('#is-campaign-modal');

    // Open modal — Add
    $('#is-campaign-add-btn').on('click', function () {
        resetForm($('#is-campaign-form'));
        $('#is-campaign-modal-title').text('Ajouter une campagne');
        $('#is-campaign-id').val('');
        // uncheck all choices
        $campaignModal.find('input[name="choices[]"]').prop('checked', false);
        openModal($campaignModal);
    });

    // Open modal — Edit (campaigns table)
    $(document).on('click', '#is-campaigns-tbody .is-btn-edit', function () {
        var id = $(this).data('id');
        resetForm($('#is-campaign-form'));
        $('#is-campaign-modal-title').text('Modifier la campagne');

        $.post(ajaxUrl, { action: 'inssetsup_campaign_get', nonce: nonce, id_campaign: id })
            .done(function (res) {
                if (!res.success) return;
                var c = res.data.campaign;
                var choiceIds = res.data.choice_ids;

                $('#is-campaign-id').val(c.id_campaign);
                $('#is-campaign-name').val(c.name_campaign);
                $('#is-campaign-desc').val(c.desc_campaign);
                $('#is-campaign-start').val(c.startdate ? c.startdate.substring(0, 10) : '');
                $('#is-campaign-end').val(c.end_date   ? c.end_date.substring(0, 10)   : '');
                $('#is-campaign-active').prop('checked', c.isactivated == 1);

                $campaignModal.find('input[name="choices[]"]').each(function () {
                    $(this).prop('checked', $.inArray($(this).val(), choiceIds) !== -1);
                });

                openModal($campaignModal);
            });
    });

    // Submit — Campaign form
    $('#is-campaign-form').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn  = $form.find('[type="submit"]');
        var $fbk  = $('#is-campaign-feedback');

        var data = {
            action:        'inssetsup_campaign_save',
            nonce:         nonce,
            id_campaign:   $('#is-campaign-id').val(),
            name_campaign: $('#is-campaign-name').val(),
            desc_campaign: $('#is-campaign-desc').val(),
            startdate:     $('#is-campaign-start').val(),
            end_date:      $('#is-campaign-end').val(),
            isactivated:   $('#is-campaign-active').is(':checked') ? 1 : 0,
        };

        // Collect checked choices
        data['choices[]'] = [];
        $form.find('input[name="choices[]"]:checked').each(function () {
            data['choices[]'].push($(this).val());
        });

        $btn.prop('disabled', true);

        $.post(ajaxUrl, data)
            .done(function (res) {
                if (res.success) {
                    closeModal($campaignModal);
                    location.reload();
                } else {
                    setFeedback($fbk, 'error', res.data.message);
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                setFeedback($fbk, 'error', 'Erreur réseau.');
                $btn.prop('disabled', false);
            });
    });

    // Delete — Campaign
    $(document).on('click', '#is-campaigns-tbody .is-btn-delete', function () {
        var id   = $(this).data('id');
        var name = $(this).closest('tr').find('strong').text();

        if (!window.confirm('Supprimer la campagne « ' + name + ' » ?\nImpossible si des étudiants ont déjà fait des choix.'))
            return;

        $.post(ajaxUrl, { action: 'inssetsup_campaign_delete', nonce: nonce, id_campaign: id })
            .done(function (res) {
                if (res.success)
                    location.reload();
                else
                    alert(res.data.message);
            });
    });

}(jQuery));
