<?php
/**
 * Plugin Name: InssetSup
 * Description: Clone simplifié de Parcoursup — gestion des campagnes d'orientation
 *              et des vœux étudiants, avec back-office WordPress.
 * Version: 1.0.0
 */

// Bloquer l'accès direct au fichier hors contexte WordPress.
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('INSSETSUP_FILE')) {

    // Constantes globales du plugin.
    define('INSSETSUP_FILE', __FILE__);
    define('INSSETSUP_DIR', dirname(INSSETSUP_FILE));

    // Chargement automatique de toutes les classes situées dans classes/*/*.php
    foreach (glob(INSSETSUP_DIR . '/classes/*/*.php') as $filename)
        if (!@require_once $filename)
            throw new Exception(sprintf(__('Failed to include %s'), $filename));

    // À l'activation du plugin : création des tables DB + pages WordPress.
    register_activation_hook(INSSETSUP_FILE, function() {
        $install = new InssetSup_Install_Index();
        $install->setup();
    });

    // Chargement du bon contrôleur principal selon le contexte de la requête.
    if (is_admin())
        new InssetSup_Main_Admin();  // Back-office WordPress
    else
        new InssetSup_Main_Front(); // Site public (pages étudiants)

}
