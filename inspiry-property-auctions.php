<?php
/**
 * Plugin Name: Inspiry Property Auctions
 * Plugin URI: http://themeforest.net/item/real-homes-wordpress-real-estate-theme/5373914
 * Description: This plugin provides auction functionality for property post type.
 * Version: 1.0
 * Author: Inspiry Themes
 * Author URI: https://themeforest.net/user/inspirythemes/portfolio?order_by=sales
 * Text Domain: inspiry-property-auctions
 * Domain Path: /languages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Inspiry_Property_Auctions' ) ) :

	final class Inspiry_Property_Auctions {

		/**
		 * Plugin's current version
		 *
		 * @var string
		 */
		public $version;

		/**
		 * Plugin Name
		 *
		 * @var string
		 */
		public $plugin_name;

		/**
		 * Plugin's singleton instance.
		 *
		 * @var Inspiry_Property_Auctions
		 */
		protected static $_instance;

		/**
		 * Constructor function.
		 */
		public function __construct() {

			$this->plugin_name = 'inspiry-property-auctions';
			$this->version     = '1.0';

			$this->define_constants();

			$this->includes();

			$this->initialize_meta_boxes();

			$this->init_hooks();

			do_action( 'ipa_loaded' );  // Inspiry Property Auctions plugin loaded action hook.
		}

		/**
		 * Provides singleton instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Defines constants.
		 */
		protected function define_constants() {

			if ( ! defined( 'IPA_VERSION' ) ) {
				define( 'IPA_VERSION', $this->version );
			}

			// Full path and filename.
			if ( ! defined( 'IPA_PLUGIN_FILE' ) ) {
				define( 'IPA_PLUGIN_FILE', __FILE__ );
			}

			// Plugin directory path.
			if ( ! defined( 'IPA_PLUGIN_DIR' ) ) {
				define( 'IPA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin directory URL.
			if ( ! defined( 'IPA_PLUGIN_URL' ) ) {
				define( 'IPA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin file path relative to plugins directory.
			if ( ! defined( 'IPA_PLUGIN_BASENAME' ) ) {
				define( 'IPA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			}

		}

		/**
		 * Includes files required on admin and on frontend.
		 */
		public function includes() {
			$this->include_basic_functions();
		}

		/**
		 * Shortcodes
		 */
		public function include_basic_functions() {
			include_once IPA_PLUGIN_DIR . 'includes/basic-functions.php';
		}

		/**
		 * Meta boxes
		 */
		public function initialize_meta_boxes() {
			include_once IPA_PLUGIN_DIR . 'includes/class-ipa-meta-boxes.php';
		}

		/**
		 * Initialize hooks.
		 */
		public function init_hooks() {
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );  // plugin's admin styles.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) ); // plugin's admin scrips.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) ); // plugin's scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) ); // plugin's scripts.
            $ipa_meta_alter = new IPA_Meta_Boxes();
            add_filter( 'framework_theme_meta', array( $ipa_meta_alter, 'ipa_additional_meta_boxes' ), 99 );
            add_action( 'add_meta_boxes', array( $ipa_meta_alter, 'ipa_auction_bids_meta_boxes' ), 99 );
		}

		/**
		 * Load text domain for translation.
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'inspiry-property-auctions', false, dirname( IPA_PLUGIN_BASENAME ) . '/languages' );
		}

		/**
		 * Enqueue admin styles
		 */
		public function enqueue_admin_styles() {
			wp_enqueue_style( 'inspiry-property-auctions-admin', IPA_PLUGIN_URL . 'css/ipa-admin.css', array(), $this->version, 'all' );
		}

		/**
		 * Enqueue Admin JavaScript
		 */
		public function enqueue_admin_scripts() {
			wp_enqueue_script('inspiry-property-auctions-admin', IPA_PLUGIN_URL . 'js/ipa-admin.js', array('jquery',), $this->version, true);
		}

        /**
         * Enqueue Styles
         */
        public function enqueue_styles() {
            wp_enqueue_style( 'ipa-frontend', IPA_PLUGIN_URL . 'css/ipa-frontend.css', array(), $this->version, 'all' );
        }

		/**
		 * Enqueue JavaScript
		 */
		public function enqueue_scripts() {
            wp_enqueue_script( 'ipa-frontend', IPA_PLUGIN_URL . 'js/ipa-frontend.js', array( 'jquery' ), $this->version, true );
		}



		/**
		 * Add notice when settings are saved.
		 */
		public function notice() {
			?>
            <div id="setting-error-ere_settings_updated" class="updated notice is-dismissible">
                <p><strong><?php esc_html_e( 'Settings saved successfully!', 'easy-real-estate' ); ?></strong></p>
            </div>
			<?php
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning is forbidden!', 'easy-real-estate' ), IPA_VERSION );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing is forbidden!', 'easy-real-estate' ), IPA_VERSION );
		}

	}

endif; // End if class_exists check.

// run on IPA activation
function ipa_plugin_activated(){
	add_option( 'ipa_plugin_activated', true );
}
register_activation_hook( __FILE__, 'ipa_plugin_activated' );

/**
 * Main instance of Inspiry_Property_Auctions.
 * Returns the main instance of Inspiry_Property_Auctions to prevent the need to use globals.
 * @return Inspiry_Property_Auctions
 */
function IPA() {
	return Inspiry_Property_Auctions::instance();
}

// Get IPA Running.
IPA();
