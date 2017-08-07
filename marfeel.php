<?php
/*
    Plugin Name: Marfeel
    Plugin URI:  http://www.marfeel.com
    Description: Marfeel configuration for Wordpress sites.
    Version:     1.5.4
    Author:      Marfeel Team
    Author URI:  http://www.marfeel.com
    License:     GPL2
    License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('MARFEEL_OPTIONS', 'marfeel_options');

require_once('marfeel_troy.php');
require_once('marfeel_wordpress_helper.php');

register_activation_hook(__FILE__, 'activate_marfeel_plugin');
register_deactivation_hook(__FILE__, 'deactivate_marfeel_plugin');

add_action('admin_init', 'register_marfeel_options');
add_action('admin_menu', 'register_marfeel_settings_page' );
add_action('wp_head', 'render_marfeel_amp_link' );

function activate_marfeel_plugin() {
    $marfeel_domain = MarfeelTroy::getInstance()->get_marfeel_domain_for_uri($_SERVER['SERVER_NAME']);

    if (isset($marfeel_domain)) {
        MarfeelWordpressHelper::getInstance()->save_option('marfeel_domain', $marfeel_domain);
    } else {
        MarfeelWordpressHelper::getInstance()->delete_all_options();
    }
}

function deactivate_marfeel_plugin() {
    MarfeelWordpressHelper::getInstance()->delete_all_options();
}

function register_marfeel_options() {
    register_setting(MARFEEL_OPTIONS, MARFEEL_OPTIONS, 'validate_marfeel_options');
}

function validate_marfeel_options($options) {
    $sanitized_domain = filter_var(trim($options['marfeel_domain']), FILTER_SANITIZE_SPECIAL_CHARS);

    if (strpos($sanitized_domain, '.marfeel.com') === false && strpos($sanitized_domain, 'amp.') === false) {
        add_settings_error(
            'marfeel_domain',
            'marfeeldomain_texterror',
            'Invalid domain. Please contact support@marfeel.com',
            'error'
        );

        return array();
    }

    $options['marfeel_domain'] = $sanitized_domain;

    return $options;
}

function register_marfeel_settings_page() {
    add_options_page(
        'Marfeel',
        'Marfeel',
        'manage_options',
        MARFEEL_OPTIONS,
        'render_marfeel_settings_page'
    );
}

function render_marfeel_settings_page() {
    $marfeel_domain = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_domain');

    echo MarfeelTroy::getInstance()->get_settings_page($marfeel_domain);
}

function render_marfeel_amp_link() {
    $is_article = MarfeelWordpressHelper::getInstance()->is_article();

    if ($is_article) {
        $current_uri = MarfeelWordpressHelper::getInstance()->get_current_uri();
        $marfeel_domain = MarfeelWordpressHelper::getInstance()->get_option_value('marfeel_domain');

				if(strpos($marfeel_domain, 'amp.') !== false) {
					$current_uri = remove_protocol_and_domain($current_uri);
				} else {
					$current_uri = remove_protocol($current_uri);
				}

        echo MarfeelTroy::getInstance()->get_amp_link_for_uri($current_uri, $marfeel_domain);
    }
}

function remove_protocol($uri) {
		return str_replace(array('http://', 'https://'), array('', ''), $uri);
}

function remove_protocol_and_domain($uri) {
		return parse_url($uri)['path'];
}
