(function ($) {
    "use strict";

    var campaignId = (typeof InssetsupCampaign !== "undefined") ? InssetsupCampaign.campaign_id : "";

    function updateSelects() {
        var $s1 = $("#is-choice-1");
        var $s2 = $("#is-choice-2");
        var $s3 = $("#is-choice-3");
        var v1  = $s1.val();

        $s2.find("option").prop("disabled", false);
        $s3.find("option").prop("disabled", false);

        if (v1) {
            $s2.prop("disabled", false);
            $s2.find("option[value=\"" + v1 + "\"]").prop("disabled", true);
            if ($s2.val() === v1) {
                $s2.val("");
            }
        } else {
            $s2.val("").prop("disabled", true);
            $s3.val("").prop("disabled", true);
            return;
        }

        var v2 = $s2.val();

        if (v2) {
            $s3.prop("disabled", false);
            $s3.find("option[value=\"" + v1 + "\"]").prop("disabled", true);
            $s3.find("option[value=\"" + v2 + "\"]").prop("disabled", true);
            var v3 = $s3.val();
            if (v3 === v1 || v3 === v2) {
                $s3.val("");
            }
        } else {
            $s3.val("").prop("disabled", true);
        }
    }

    function showMessage($el, message, type) {
        $el.text(message)
           .removeClass("is-message--success is-message--error")
           .addClass("is-message--" + type)
           .show();
    }

    $(function () {

        // ── Déconnexion (présent sur toutes les vues campagne) ──
        $("#is-logout-btn").on("click", function () {
            var $btn = $(this);
            $btn.prop("disabled", true).text("Déconnexion...");
            $.post(
                InssetsupAuth.ajax_url,
                { action: "inssetsup_logout", nonce: InssetsupAuth.nonce },
                function (res) {
                    if (res.success && res.data.redirect) {
                        window.location.href = res.data.redirect;
                    } else {
                        $btn.prop("disabled", false).text("Déconnexion");
                    }
                }
            );
        });

        // ── Formulaire de choix ───────────────
        var $form   = $("#is-campaign-form");
        var $recap  = $("#is-recap");
        var $msg    = $("#is-campaign-message");
        var $submit = $("#is-submit-choices");

        if (!$form.length) { return; }

        if ($("#is-choice-1").val()) {
            $("#is-choice-2, #is-choice-3").prop("disabled", false);
            updateSelects();
        }

        $form.on("change", "#is-choice-1, #is-choice-2", function () {
            updateSelects();
        });

        $("#is-edit-btn").on("click", function () {
            $recap.addClass("is-hidden");
            $form.removeClass("is-hidden");
            if ($("#is-choice-1").val()) {
                $("#is-choice-2, #is-choice-3").prop("disabled", false);
                updateSelects();
            }
        });

        $form.on("submit", function (e) {
            e.preventDefault();

            var c1 = $("#is-choice-1").val();
            var c2 = $("#is-choice-2").val();
            var c3 = $("#is-choice-3").val();

            if (!c1 || !c2 || !c3) {
                showMessage($msg, "Veuillez sélectionner vos 3 choix.", "error");
                return;
            }

            if (c1 === c2 || c1 === c3 || c2 === c3) {
                showMessage($msg, "Vous ne pouvez pas sélectionner la même formation deux fois.", "error");
                return;
            }

            $submit.prop("disabled", true);
            $msg.removeClass("is-message--success is-message--error").text("").hide();

            $.post(
                InssetsupCampaign.ajax_url,
                {
                    action:      "inssetsup_save_choices",
                    nonce:       InssetsupCampaign.nonce,
                    campaign_id: campaignId,
                    choice_1:    c1,
                    choice_2:    c2,
                    choice_3:    c3
                },
                function (res) {
                    $submit.prop("disabled", false);

                    if (!res.success) {
                        showMessage($msg, res.data.message, "error");
                        return;
                    }

                    // Redirection vers la page de confirmation
                    var base = window.location.href.split("?")[0];
                    window.location.href = base + "?campaign_id=" + encodeURIComponent(campaignId) + "&confirmed=1";
                }
            );
        });
    });

}(jQuery));
