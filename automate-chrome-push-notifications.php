<?php
/*
Plugin Name: Automate Chrome Push Notifications
Plugin URI: https://www.aklamator.com/wordpress
Description: Automate Push messages for Chrome Desktop and Android visitors. Schedule notifications and ReEngage visitors with new posts or nice widget where you can sell your ad space.
Version: 2.0
Author: Aklamator
Author URI: https://www.aklamator.com/
License: GPL2

Copyright 2015 Aklamator.com (email : info@aklamator.com)

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/


/*
 * Define constants
 */
if(!defined('CHROME_PUSH_AKLA_PLUGIN_NAME'))
    define('CHROME_PUSH_AKLA_PLUGIN_NAME', plugin_basename(__FILE__));

if (!defined('CHROME_PUSH_AKLA_PLUGIN_DIR')) {
    define('CHROME_PUSH_AKLA_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('CHROME_PUSH_AKLA_PLUGIN_URL')) {
    define('CHROME_PUSH_AKLA_PLUGIN_URL', plugin_dir_url(__FILE__));
}


require_once CHROME_PUSH_AKLA_PLUGIN_DIR . 'includes/class-automate-chrome-push.php';
/*
 * Activation Hook
 */

register_activation_hook(__FILE__, array('ChromePushAutomate', 'set_up_options'));
/*
 * Uninstall Hook
 */
register_uninstall_hook(__FILE__, array('ChromePushAutomate', 'aklamator_uninstall'));

//start the plugin
ChromePushAutomate::init();


