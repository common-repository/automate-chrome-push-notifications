<?php

class ChromePushAutomate
{
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function init() {

        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    protected $aklamator_url;
    protected $api_data;
    protected $application_id;
    protected $site_key;
    protected $to_write;
    public $curlfailovao;


    public function __construct() {

        $this->aklamator_url = "https://aklamator.com/";
//        $this->aklamator_url = "http://192.168.5.60/aklamator/www/";
        $this->application_id = get_option('aklamatorChromeApplicationID');
        $this->site_key = get_option('aklamatorChromeSiteKey');
        $this->to_write = get_option('aklamatorChromeWriteFile');


        $this->hooks();

    }


    /**
     * All hooks registration goes here.
     * @return void
     */
    private function hooks()
    {

        add_filter('plugin_row_meta', array($this, 'aklamator_chrome_plugin_meta_links'), 10, 2); //Add rate and review link in plugin section
        add_filter("plugin_action_links_".CHROME_PUSH_AKLA_PLUGIN_NAME, array($this, 'aklamator_chrome_plugin_settings_link')); // Add setting link on plugin page

        if(get_option('aklamatorChromeFeatured2Feed')){ // Adds featured images from posts to your site's RSS feed output,
            add_filter('the_excerpt_rss', array($this, 'aklamator_chrome_featured_images_in_rss', 1000, 1));
            add_filter('the_content_feed', array($this, 'aklamator_chrome_featured_images_in_rss', 1000, 1));
        }

        if (isset($_GET['page']) && $_GET['page'] == 'automate-chrome-push-notifications')
        add_action('init', array($this, 'installFiles'));
        add_action("admin_menu", array($this,"adminMenu"));
        add_action('admin_init', array($this,"setOptions"));
        add_action( 'admin_enqueue_scripts', array($this, 'load_custom_wp_admin_style_script'));
        add_action('transition_post_status', array($this, 'onPostStatusChangeSendMessage'), 10, 3);
        if($this->to_write == 'no' && $this->site_key)
            add_action('wp_enqueue_scripts', array($this, 'registerPushNotificationJs'));

        add_action('add_meta_boxes', array($this, 'addMetaBox'));

        if (!$this->checkSSL()) {
            add_action('admin_notices', array($this, 'checkSiteConfigNotice'));
        }

    }

    function load_custom_wp_admin_style_script($hook) {

        if ( 'toplevel_page_automate-chrome-push-notifications' != $hook ) {
            return;
        }
        // Load necessary css files
        wp_enqueue_style( 'custom-admin-css', CHROME_PUSH_AKLA_PLUGIN_URL . 'assets/css/admin-style.css', false, '1.0.0' );
        wp_enqueue_style( 'dataTables-plugin', CHROME_PUSH_AKLA_PLUGIN_URL . 'assets/dataTables/jquery.dataTables.min.css', false, '1.10.5', false );

        // Load script files
        wp_enqueue_script( 'dataTables-plugin', CHROME_PUSH_AKLA_PLUGIN_URL . 'assets/dataTables/jquery.dataTables.min.js', array('jquery'), '1.10.5', true );
        wp_enqueue_script( 'custom-admin-script', CHROME_PUSH_AKLA_PLUGIN_URL . 'assets/js/admin-main.js', array('jquery'), '1.0', true);

    }

    function setOptions() {

        register_setting('aklamatorChrome-options', 'aklamatorChromeApplicationID');
        register_setting('aklamatorChrome-options', 'aklamatorChromePoweredBy');
        register_setting('aklamatorChrome-options', 'aklamatorChromeFeatured2Feed');
        register_setting('aklamatorChrome-options', 'aklamatorChromePostTypes');

    }

    /**
     * Admin menus registration
     * @return void
     */

    public function adminMenu() {
        add_menu_page('Chrome Automate Push', 'Automate <br>Chrome Push', 'manage_options', 'automate-chrome-push-notifications', array($this, 'createAdminPage'), CHROME_PUSH_AKLA_PLUGIN_URL.'images/aklamator-icon.png');
    }


    public function getSignupUrl()
    {
        $user_info =  wp_get_current_user();

        return $this->aklamator_url . 'login/application_id?utm_source=wordpress&utm_medium=wpchromepush&e=' . urlencode(get_option('admin_email')) .
        '&pub=' .  preg_replace('/^www\./','',$_SERVER['SERVER_NAME']).
        '&un=' . urlencode($user_info->user_login). '&fn=' . urlencode($user_info->user_firstname) . '&ln=' . urlencode($user_info->user_lastname) .
        '&pl=chrome-push&return_uri=' . admin_url("admin.php?page=automate-chrome-push-notifications");

    }
    /**
     * Https requirement Notice
     * @return void
     */
    public function checkSiteConfigNotice() {
        ?>
        <div class="error">
            <p>Your Site URL should be set to HTTPS for Chrome Push Notifications Plugin to work.</p>
        </div>
        <?php
    }

    /**
     * Check if Site url is set to HTTPS
     * @return void
     */
    private function checkSSL() {
        return strpos(get_option('siteurl'), 'https://') !== false;
    }

    private function addNewWebsiteApi()
    {

        if (!is_callable('curl_init')) {
            return;
        }


        $service     = $this->aklamator_url . "wp-push/authenticate";
        $p['ip']     = $_SERVER['REMOTE_ADDR'];
        $p['domain'] = site_url();
        $p['source'] = "wordpress_chrome_push";
        $p['AklamatorApplicationID'] = $this->application_id;


        $client = curl_init();

        curl_setopt($client, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($client, CURLOPT_HEADER, 0);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_URL, $service);

        if (!empty($p)) {
            curl_setopt($client, CURLOPT_POST, count($p));
            curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($p));
        }

        $data = curl_exec($client);
        if (curl_error($client)!= "") {
            $this->curlfailovao=1;
        } else {
            $this->curlfailovao=0;
        }

        curl_close($client);

        $data = json_decode($data);

        return $data;

    }
    /*
     * Handle sending push messages to all subscribers trough aklamator API
     */
    private function sendPushMessage($p) {

        if (!is_callable('curl_init')) {
            return;
        }


        $service = $this->aklamator_url . "wp-send/message";

        $client = curl_init();

        curl_setopt($client, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($client, CURLOPT_HEADER, 0);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($client, CURLOPT_URL, $service);

        if (!empty($p)) {
            curl_setopt($client, CURLOPT_POST, count($p));
            curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($p));
        }

        $data = curl_exec($client);
        if (curl_error($client)!= "") {
            $this->curlfailovao=1;
        } else {
            $this->curlfailovao=0;
        }

        curl_close($client);

        $data = json_decode($data);

        return $data;

    }

    // Set Up options
    public function set_up_options() {
        add_option('aklamatorChromeApplicationID', '');
        add_option('aklamatorChromeSiteKey', '');
        add_option('aklamatorChromePoweredBy', '');
        add_option('aklamatorChromeFeatured2Feed', 'on');
        add_option('aklamatorChromeWriteFile', 'yes');
        add_option('aklamatorChromePostTypes', array('page', 'post'));
    }

    // Delete options upon uninstall
    public function aklamator_uninstall() {
        delete_option('aklamatorChromeApplicationID');
        delete_option('aklamatorChromeSiteKey');
        delete_option('aklamatorChromePoweredBy');
        delete_option('aklamatorChromeFeatured2Feed');
        delete_option('aklamatorChromeWriteFile');
        delete_option('aklamatorChromeWriteFile');
        delete_option('aklamatorChromePostTypes');

    }

    // Add settings link on plugin page
    function aklamator_chrome_plugin_settings_link($links) {
        $settings_link = '<a href="admin.php?page=automate-chrome-push-notifications">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /*
     * Add rate and review link in plugin section
     */
    function aklamator_chrome_plugin_meta_links($links, $file) {
        $plugin = CHROME_PUSH_AKLA_PLUGIN_NAME;
        // create link
        if ($file == $plugin) {
            return array_merge(
                $links,
                array('<a href="https://wordpress.org/support/plugin/automate-chrome-push-notifications/reviews" target=_blank>Please rate and review</a>')
            );
        }
        return $links;
    }

    /*
     * Adds featured images from posts to your site's RSS feed output,
     */

    function aklamator_chrome_featured_images_in_rss($content){
        global $post;
        if (has_post_thumbnail($post->ID)) {
            $featured_images_in_rss_size = 'thumbnail';
            $featured_images_in_rss_css_code = 'display: block; margin-bottom: 5px; clear:both;';
            $content = get_the_post_thumbnail($post->ID, $featured_images_in_rss_size, array('style' => $featured_images_in_rss_css_code)) . $content;
        }
        return $content;
    }

    public function createAdminPage() {

        include_once CHROME_PUSH_AKLA_PLUGIN_DIR . 'views/admin-page.php';
    }

    /**
     * Write File generic function.
     * @param  String $form_url     URL of the form to return if no permissions
     * @param  String $file_content Content of the file to be writen
     * @param  String $filename     Name of the new file
     * @return void
     */
    public static function writeFile($form_url, $file_content, $filename, $context = ABSPATH)
    {
        global $wp_filesystem;

        $method = '';

        if (!self::initFilesystem($form_url, $method, ABSPATH)) {
            return false;
        }

        $target_dir = $wp_filesystem->find_folder($context);
        $target_file = trailingslashit($target_dir) . $filename;

        if (!$wp_filesystem->put_contents($target_file, $file_content, FS_CHMOD_FILE)) {
            return new WP_Error('writing_error', 'Error when writing file');
        }

        return $file_content;
    }

    /**
     * Initialization of the FileSystem class
     * @param  String $form_url
     * @param  String $method
     * @param  String $context
     * @param  String $fields
     * @return void
     */
    public static function initFilesystem($form_url, $method, $context, $fields = null)
    {
        global $wp_filesystem;
        include_once ABSPATH . 'wp-admin/includes/file.php';
        if (false === ($creds = request_filesystem_credentials($form_url, $method, false, $context, $fields))) {

            return false;
        }

        if (!WP_Filesystem($creds)) {

            request_filesystem_credentials($form_url, $method, true, $context);
            return false;
        }

        return true; //filesystem object successfully initiated
    }

    /**
     * ServiceWorker (service-worker.js) creation
     * @return void
     */
    public function writeServiceWorker() {

        $tmp_sw = file_get_contents(CHROME_PUSH_AKLA_PLUGIN_DIR . 'assets/temp/service-worker_temp.txt');
        $tmp_sw = str_replace('siteKey:', "siteKey: '".$this->site_key."',", $tmp_sw);

        $form_url = 'admin.php?chrome-push-automate-notifications';
        return self::writeFile($form_url, $tmp_sw, 'service-worker.js');

    }

    /**
     * ServiceWorker (chrome_push_script.js) creation in plugin assets
     * @return void
     */
    public function writeChromePushScript() {

        $tmp_sw = file_get_contents(CHROME_PUSH_AKLA_PLUGIN_DIR . 'assets/temp/index_temp.txt');
        $tmp_sw = str_replace('siteKey:', "siteKey: '".$this->site_key."',", $tmp_sw);

        $form_url = 'admin.php?chrome-push-automate-notifications';
        return self::writeFile($form_url, $tmp_sw, 'chrome_push_script.js', CHROME_PUSH_AKLA_PLUGIN_DIR .'assets/js/');

    }

    /**
     * Manifest (manifest.json) creation
     * @return void
     */
    public function writeManifest() {

        $tmp_sw = file_get_contents(CHROME_PUSH_AKLA_PLUGIN_DIR . 'assets/temp/manifest.txt');

        $form_url = 'admin.php?chrome-push-automate-notifications';
        return self::writeFile($form_url, $tmp_sw, 'manifest.json');

    }

    public function installFiles() {


        if ($this->application_id !== '') {
            $this->api_data = $this->addNewWebsiteApi();

            if($this->api_data->flag && isset($this->api_data->data->site_key) && $this->to_write == 'yes'){

                // update options
                update_option('aklamatorChromeSiteKey', $this->api_data->data->site_key);
                $this->site_key = $this->api_data->data->site_key;

                if($this->writeServiceWorker() && $this->writeChromePushScript() && $this->writeManifest()){
                    update_option('aklamatorChromeWriteFile', 'no');
                    $this->to_write = 'no';
                }


            }

        }

    }

    /**
     * Fix http to https
     * @param  string $url
     * @return string
     */
    private function fixHttpsURL($url)
    {
        if (stripos($url, 'http://') === 0) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }

    public function get_week($offset){
        $account_options = array(0=>'Monday', 1 => 'Tuesday', 2 => 'Wednesday', 3=> 'Thursday', 4 => 'Friday', 5 => 'Saturday', 6 => 'Sunday');

        return $account_options[$offset];
    }

    /**
     * Register the JS files and vars
     * @return void
     */
    public function registerPushNotificationJs() {

        wp_register_script('akla-chrome-push', $this->fixHttpsURL(CHROME_PUSH_AKLA_PLUGIN_URL) . 'assets/js/chrome_push_script.js', array(), true, false);
        wp_enqueue_script('akla-chrome-push');

    }

    /**
     * The function to add Metabox in posts
     */
    public function addMetaBox() {

        $screens = get_option('aklamatorChromePostTypes');

        foreach ($screens as $screen) {

            add_meta_box(
                'akla-chrome-push',
                'Chrome Push Notifications',
                array($this, 'metaboxCallback'),
                $screen,
                'side',
                'high'
            );
        }
    }

    /**
     * The meta box callback function
     * @return void
     */
    public function metaboxCallback() {
        echo '<input type="checkbox" id="akla_chrome_push_confirm" name="akla_chrome_push_confirm" value="yes" checked="checked"> Send push notification ';
    }


    public function onPostStatusChangeSendMessage($new_status, $old_status, $post) {

        if (($old_status != $new_status && $new_status == 'publish') || ($old_status == 'future' && $new_status == 'publish')) {
            if (isset($_POST['akla_chrome_push_confirm']) && !empty($_POST['akla_chrome_push_confirm']) && !empty($post->post_title) && !empty($post->post_content)) {

                $selected_post_types = get_option('aklamatorChromePostTypes');
                if (is_array($selected_post_types) && in_array($post->post_type, $selected_post_types)) {
                    $data = array(
                        'title' => $post->post_title,
                        'body' => mb_substr(wp_strip_all_tags(strip_shortcodes($post->post_content)), 0, 120),
                        'url' => get_permalink($post->ID),
                        'account_id' => $this->api_data->data->id
                    );

                    $this->sendPushMessage($data);
                }
            }

        }

    }


}