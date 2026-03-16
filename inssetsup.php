<?php
/**
 * Plugin Name: InssetSup
 * Description: Structure de base du plugin InssetSup.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('INSSETSUP_FILE')) {

    define('INSSETSUP_FILE', __FILE__);
    define('INSSETSUP_DIR', dirname(INSSETSUP_FILE));

    foreach (glob(INSSETSUP_DIR . '/classes/*/*.php') as $filename)
        if (!@require_once $filename)
            throw new Exception(sprintf(__('Failed to include %s'), $filename));

    register_activation_hook(INSSETSUP_FILE, function() {
        $install = new InssetSup_Install_Index();
        $install->setup();
    });

    if (!is_admin())
        new InssetSup_Main_Front();

}
