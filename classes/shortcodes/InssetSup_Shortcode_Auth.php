<?php

/**
 * Shortcode [inssetsup_auth] — Page de connexion / inscription.
 *
 * Affiche deux formulaires (connexion + création de compte) accessibles
 * via des onglets. Si l'étudiant est déjà connecté, il est redirigé
 * directement vers la page des campagnes.
 *
 * Les soumissions sont traitées en AJAX par InssetSup_Actions_Auth.
 */

class InssetSup_Shortcode_Auth {

    public static function render() {
        if (InssetSup_Helper_Auth::is_student_logged_in()) {
            wp_redirect(home_url('/campagne/'));
            exit;
        }

        ob_start();
        ?>
        <div class="inssetsup-auth-wrap">
            <div class="auth-card">

                <div class="auth-card__header">
                    <div class="auth-logo">
                        <span class="auth-logo__badge">InssetSup</span>
                        <span class="auth-logo__text">Parcours SupWP</span>
                    </div>
                    <h2>Accès candidat</h2>
                </div>

                <div class="auth-card__body">
                <div class="auth-tabs">
                    <button class="tab-btn active" data-tab="login">Connexion</button>
                    <button class="tab-btn" data-tab="register">Créer un compte</button>
                </div>

                <!-- Formulaire de connexion -->
                <form class="auth-form active" id="inssetsup-login-form" data-action="inssetsup_login" novalidate>
                    <div class="form-group">
                        <label for="login-email">Adresse email</label>
                        <input type="email" id="login-email" name="email" placeholder="etudiant@exemple.fr" autocomplete="email" required />
                    </div>
                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <input type="password" id="login-password" name="password" placeholder="••••••••" autocomplete="current-password" required />
                    </div>
                    <button type="submit" class="btn-submit">Se connecter</button>
                    <div class="auth-message" id="login-message"></div>
                </form>

                <!-- Formulaire de création de compte -->
                <form class="auth-form" id="inssetsup-register-form" data-action="inssetsup_register" novalidate>
                    <div class="form-group">
                        <label for="reg-fname">Prénom</label>
                        <input type="text" id="reg-fname" name="fname" placeholder="Jean" autocomplete="given-name" required />
                    </div>
                    <div class="form-group">
                        <label for="reg-lname">Nom</label>
                        <input type="text" id="reg-lname" name="lname" placeholder="Dupont" autocomplete="family-name" required />
                    </div>
                    <div class="form-group">
                        <label for="reg-email">Adresse email</label>
                        <input type="email" id="reg-email" name="email" placeholder="etudiant@exemple.fr" autocomplete="email" required />
                    </div>
                    <div class="form-group">
                        <label for="reg-password">Mot de passe <span>(8 caractères minimum)</span></label>
                        <input type="password" id="reg-password" name="password" placeholder="••••••••" autocomplete="new-password" required />
                    </div>
                    <button type="submit" class="btn-submit">Créer mon compte</button>
                    <div class="auth-message" id="register-message"></div>
                </form>
                </div>

            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
