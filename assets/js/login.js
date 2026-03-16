(function ($) {
    'use strict';

    // ── Tab switching ─────────────────────────────────────────────────
    $(document).on('click', '.inssetsup-auth-wrap .tab-btn', function () {
        var tab = $(this).data('tab');

        $('.inssetsup-auth-wrap .tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.inssetsup-auth-wrap .auth-form').removeClass('active');
        $('#inssetsup-' + tab + '-form').addClass('active');

        $('.auth-message').removeClass('success error').hide().text('');
    });

    // ── Generic AJAX form handler ─────────────────────────────────────
    function submitAuthForm($form) {
        var action  = $form.data('action');
        var formId  = $form.attr('id').replace('inssetsup-', '').replace('-form', '');
        var $msgEl  = $('#' + formId + '-message');
        var $btn    = $form.find('.btn-submit');

        $msgEl.removeClass('success error').hide().text('');
        $btn.prop('disabled', true);

        var data = {
            action: action,
            nonce:  InssetsupAuth.nonce
        };

        $form.find('input[name]').each(function () {
            data[$(this).attr('name')] = $(this).val();
        });

        $.post(InssetsupAuth.ajax_url, data)
            .done(function (res) {
                if (res.success) {
                    $msgEl.addClass('success').text('Connexion réussie, redirection…').show();
                    window.location.href = res.data.redirect;
                } else {
                    $msgEl.addClass('error').text(res.data.message).show();
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                $msgEl.addClass('error').text('Erreur réseau, veuillez réessayer.').show();
                $btn.prop('disabled', false);
            });
    }

    // ── Bind forms ────────────────────────────────────────────────────
    $(document).on('submit', '#inssetsup-login-form, #inssetsup-register-form', function (e) {
        e.preventDefault();
        submitAuthForm($(this));
    });

}(jQuery));
