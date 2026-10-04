<?php
/**
 * Plugin Name: Arkana Civiel Legal Management — Local V7 Test Bundle
 * Description: Local QA bundle that boots the Arkana Civiel V5 core and V7 modules from one plugin activation.
 * Version: 7.17.0-local.1
 * Author: Arkana Civiel Law Firm
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * License: GPL-2.0-or-later
 * Text Domain: arkana-civiel-local-v7-test
 */

defined('ABSPATH') || exit;

final class ACLM_Local_V7_Test_Bundle {
    const CORE = __DIR__ . '/arkana-civiel-legal-management.php';

    public static function boot() {
        if (file_exists(self::CORE) && !class_exists('Arkana_Civiel_Legal_Management')) {
            require_once self::CORE;
        }

        $files = glob(__DIR__ . '/v7-*.php');
        if (is_array($files)) {
            sort($files, SORT_STRING);
            foreach ($files as $file) {
                require_once $file;
            }
        }
    }

    public static function activate() {
        self::boot();
        if (class_exists('Arkana_Civiel_Legal_Management') && method_exists('Arkana_Civiel_Legal_Management', 'activate')) {
            Arkana_Civiel_Legal_Management::activate();
        }
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}

ACLM_Local_V7_Test_Bundle::boot();
register_activation_hook(__FILE__, array('ACLM_Local_V7_Test_Bundle', 'activate'));
register_deactivation_hook(__FILE__, array('ACLM_Local_V7_Test_Bundle', 'deactivate'));
