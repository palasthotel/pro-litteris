<?php

/**
 * Plugin Name: ProLitteris
 * Description: Integration of prolitteris.ch services.
 * Version: 1.6.1
 * Author: Palasthotel <rezeption@palasthotel.de> (Edward Bock)
 * Author URI: https://palasthotel.de
 * Text Domain: pro-litteris
 * Domain Path: /languages
 */

namespace Palasthotel\ProLitteris;

// If this file is called directly, abort.

if (! defined('ABSPATH')) {
    die;
}

// composer package name is defined in plugins composer.json
const COMPOSER_PACKAGE = 'palasthotel/pro-litteris';

$centralAutoloader = (defined('PALASTHOTEL_COMPOSER_CENTRAL') && constant('PALASTHOTEL_COMPOSER_CENTRAL'))
    || did_action('palasthotel/central_autoloader_loaded') > 0;

$managedByCentralAutoloader = false;
if ($centralAutoloader && class_exists('\Composer\InstalledVersions', false)) { //checks if autoloader exists
    try {
        if (\Composer\InstalledVersions::isInstalled(COMPOSER_PACKAGE)) { // this only checks for some version not the directory 
            $installPath = \Composer\InstalledVersions::getInstallPath(COMPOSER_PACKAGE);
            $managedByCentralAutoloader = $installPath && realpath($installPath) && realpath($installPath) === realpath(__DIR__); // check if the it is acutally THIS version and dir installed
        }
    } catch (\Throwable $e) {
    }
}

if (!$centralAutoloader || !$managedByCentralAutoloader) {
    $local = __DIR__ . '/vendor/autoload.php';
    if (is_readable($local)) {
        require_once $local;
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>Bitte "composer install" im Plugin-Ordner ausführen.</p></div>';
        });
        return;
    }
}

/**
 * @property PostsTable postList
 * @property Post post
 * @property User user
 * @property TrackingPixel pixel
 * @property Database database
 * @property Repository repository
 * @property API api
 * @property Schedule schedule
 * @property DashboardWidget dashboardWidget
 * @property WP_REST rest
 * @property Assets assets
 * @property Gutenberg gutenberg
 * @property Migrate migrate
 * @property Media $media
 */
class Plugin extends Components\Plugin
{

    /**
     * Domain for translation
     */
    const DOMAIN = "pro-litteris";

    /**
     * ids
     */
    const DASHBOARD_WIDGET_ID = "pro_litteris_dashboard";

    /**
     * handles
     */
    const HANDLE_GUTENBERG_JS = "pro_litteris_gutenberg_script";
    const HANDLE_GUTENBERG_CSS = "pro_litteris_gutenberg_style";

    /**
     * Schedules
     */
    const SCHEDULE_REFILL_PIXEL_POOL = "pro_litteris_schedule_refill_pixel_pool";

    /**
     * actions
     */
    const ACTION_BEFORE_MESSAGE_CONTENT = "pro_litteris_before_message_content";
    const ACTION_AFTER_MESSAGE_CONTENT = "pro_litteris_after_message_content";

    /**
     * filters
     */
    const FILTER_PREVENT_PIXEL_ASSIGN = "pro_litteris_prevent_pixel_assign";
    const FILTER_POST_MESSAGE_CONTENT = "pro_litteris_post_message_content";
    const FILTER_POST_AUTHORS = "pro_litteris_post_authors";
    const FILTER_POST_TYPES = "pro_litteris_post_types";
    const FILTER_RENDER_PIXEL = "pro_litteris_render_pixel";
    const FILTER_POST_HAS_PAYWALL = "pro_litteris_post_has_paywall";

    /**
     * Options
     */
    const OPTION_PIXEL_POOL_SIZE = "_pro_litteris_pixel_pool_size";
    const OPTION_MIN_CHAR_COUNT = "_pro_litteris_min_char_count";

    /**
     * User meta fields
     */
    const USER_META_PRO_LITTERIS_ID = "_pro_litteris_id";
    const USER_META_PRO_LITTERIS_NAME = "_pro_litteris_name";
    const USER_META_PRO_LITTERIS_SURNAME = "_pro_litteris_surname";

    /**
     * error codes
     */
    const ERROR_CODE_CONFIG = 'pro-litteris-config-error';
    const ERROR_CODE_REQUEST = 'pro-litteris-request-error';
    const ERROR_CODE_RESPONSE = 'pro-litteris-response-error';
    const ERROR_CODE_ASSIGN_PIXEL = 'pro-litteris-assigned-pixel';
    const ERROR_CODE_PUSH_MESSAGE = 'pro-litteris-push-message';

    const POST_META_PUSH_MESSAGE_ERROR = "_pro-litteris-push-message-error";
    const POST_META_PUSH_MESSAGE_ERROR_DATA = "_pro-litteris-push-message-error-data";
    const ATTACHMENT_META_AUTHOR = "pro_litteris_attachment_author";

    /**
     * rest fields
     */
    const REST_FIELD = "pro_litteris";
    const REST_FIELD_ATTACHMENT_AUTHOR = "pro_litteris_author";

    /**
     * Plugin constructor
     */
    public function onCreate()
    {

        /**
         * load translations
         */
        $this->loadTextdomain(
            Plugin::DOMAIN,
            "languages"
        );

        // ----------------------------------------
        // all about data
        // ----------------------------------------
        $this->database   = new Database();
        $this->api        = new API();
        $this->repository = new Repository($this);
        $this->rest       = new WP_REST($this);
        $this->assets     = new Assets($this);
        $this->migrate    = new Migrate($this);

        // ----------------------------------------
        // tasks
        // ----------------------------------------
        $this->schedule = new Schedule($this);

        // ----------------------------------------
        // user interaction
        // ----------------------------------------
        $this->dashboardWidget = new DashboardWidget($this);
        $this->gutenberg       = new Gutenberg($this);
        $this->post            = new Post($this);
        $this->postList        = new PostsTable($this);
        $this->user            = new User($this);
        $this->media           = new Media($this);
        $this->pixel           = new TrackingPixel($this);

        if (WP_DEBUG) {
            $this->database->createTables();
        }
    }

    public function isEnabled()
    {
        return defined('PH_PRO_LITTERIS') && true === PH_PRO_LITTERIS;
    }

    public function hasConfig()
    {
        return defined('PH_PRO_LITTERIS_SYSTEM') && is_string(PH_PRO_LITTERIS_SYSTEM)
            && defined('PH_PRO_LITTERIS_CREDENTIALS') && is_string(PH_PRO_LITTERIS_CREDENTIALS);
    }

    /**
     * on plugin activation
     */
    function onSiteActivation()
    {
        $this->database->createTables();
    }
}

Plugin::instance();

require_once dirname(__FILE__) . "/cli/wp-cli.php";
