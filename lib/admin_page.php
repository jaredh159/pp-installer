<?php

/**
 * Init the installer plugin admin page
 *
 * @return void
 */
function ppi_admin_page_init() {
    wp_enqueue_style('ppi_css', PPI_URL . 'css/admin.css');
    wp_enqueue_script('ppi_js', PPI_URL . 'js/admin.js');
    add_action('admin_head', 'ppi_bootstrap_js');
}


/**
 * Add menu item for installer plugin
 *
 * @return void
 */
function ppi_add_menu_item() {
    add_menu_page(
        'ProPhoto Installer',
        ppi_p6_is_installed() ? 'P6 Test Drive' : 'P6 Installer',
        'edit_theme_options',
        'prophoto-installer',
        'ppi_render_admin_page',
        '',
        '50'
    );
}

/**
 * Render installer admin page
 *
 * @return void
 */
function ppi_render_admin_page() {
    $phpIsCompatible = ppi_php_compatible();
    $wpIsCompatible = ppi_wp_compatible();
    $gdIsCompatible = ppi_gd_compatible();
    $jsonIsComptible = ppi_json_compatible();
    $isCompatible = $phpIsCompatible && $wpIsCompatible && $gdIsCompatible && $jsonIsComptible;

    if (! $isCompatible || ! ppi_p6_is_installed()) {
        include(PPI_DIR . '/views/pre-install.php');
        return;
    }

    if (ppi_test_driving()) {
        ppi_render_test_driving_page();
        return;
    }

    ppi_render_p6_installed_page();

}

/**
 * Render the admin page when the user is test-driving P6
 *
 * @return void
 */
function ppi_render_test_driving_page() {
    $disableTestDriveUrl = admin_url('?ppi_disable_test_drive=1');
    $goLiveUrl = admin_url('themes.php?activated=true&ppi_go_live=1');
    include(PPI_DIR . '/views/test-driving.php');
}

/**
 * Render admin page when P6 is installed but not active or being test-driven
 *
 * @return void
 */
function ppi_render_p6_installed_page() {
    $testDriveUrl = admin_url('?ppi_enable_test_drive=1');
    $activateUrl = ppi_activate_p6_link();
    include(PPI_DIR . '/views/installed.php');
}

/**
 * Render recommendations for P6
 *
 * @return void
 */
function ppi_render_recommendations() {
    $phpOutdated = version_compare('5.5', PHP_VERSION) === 1;
    $memoryLimit = (int) ini_get('memory_limit');
    $memoryLimitLow = $memoryLimit < 256;
    $missingImagick = class_exists('Imagick');

    if ($phpOutdated || $memoryLimitLow || $missingImagick) {
        include(PPI_DIR . '/views/recommendations.php');
    }
}

/**
 * Render test-drive or installation info, based on install/token status
 *
 * @return void
 */
function ppi_render_install_or_test_drive() {
    if (ppi_p6_is_installed()) {
        ppi_render_test_drive();
        return;
    }

    $token = ppi_get_token();
    if ($token) {
        ppi_render_install_from_token();
        return;
    }
}

/**
 * Render view for installing theme from plugin token
 *
 * @return void
 */
function ppi_render_install_from_token() {
    include(PPI_DIR . '/views/install-from-token.php');
}

/**
 * Bootstrap assets to the page for javascript
 *
 * @return void
 */
function ppi_bootstrap_js() {
    $token = ppi_get_token();
    $ajaxUrl = admin_url('admin-ajax.php');

    include(PPI_DIR . '/views/bootstrap-js.php');
}

/**
 * Get the url of the ProPhoto installer plugin admin page
 *
 * @return string
 */
function ppi_get_admin_page_url() {
    return admin_url('admin.php?page=prophoto-installer');
}
