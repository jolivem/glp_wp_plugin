<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://glp-plugin.com/
 * @since      1.0.0
 *
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Geolocated_Photo
 * @subpackage Geolocated_Photo/includes
 * @author     GLP <info@glp-plugin.com>
 */
class Geolocated_Photo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Geolocated_Photo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'GLP_GALLERY_NAME_VERSION' ) ) {
			$this->version = GLP_GALLERY_NAME_VERSION;
		} else {
			$this->version = '3.0.4';
		}
		$this->plugin_name = 'geolocated-photo';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Geolocated_Photo_Loader. Orchestrates the hooks of the plugin.
	 * - Geolocated_Photo_i18n. Defines internationalization functionality.
	 * - GLP_Admin. Defines all hooks for the admin area.
	 * - Glp_Gallery_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
        if ( ! class_exists( 'WP_List_Table' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
        }
        
        /**
		 * The class responsible for defining all functions for getting all quiz data
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/geolocated-photo-gallery-data.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/geolocated-photo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/geolocated-photo-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/glp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/geolocated-photo-category-shortcode.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/glp-gallery-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/glp-map-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/geolocated-photo-extra-shortcode.php';

		/**
         * The class is responsible for showing gallery settings
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/settings/glp-settings-actions.php';

        /*
         * The class is responsible for showing galleries in wordpress default WP_LIST_TABLE style
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/glp-galleries-list-table.php';
        
        //require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/glp-categories-list-table.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/glp-maps-list-table.php';

		$this->loader = new Geolocated_Photo_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Geolocated_Photo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Geolocated_Photo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new GLP_Admin( $this->get_plugin_name(), $this->get_version() );
		$data_admin   = new Glp_Gallery_Data( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // Add menu item
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        
        $this->loader->add_action('wp_ajax_gen_glp_shortcode', $plugin_admin, 'gen_glp_shortcode_callback');
        $this->loader->add_filter("mce_external_plugins", $plugin_admin, "glp_register_tinymce_plugin");
        $this->loader->add_filter('mce_buttons', $plugin_admin, 'glp_add_tinymce_button');

        $this->loader->add_action( 'wp_ajax_deactivate_plugin_option_pm', $plugin_admin, 'deactivate_plugin_option');
        $this->loader->add_action( 'wp_ajax_nopriv_deactivate_plugin_option_pm', $plugin_admin, 'deactivate_plugin_option');

        $this->loader->add_action( 'wp_ajax_glp_author_user_search', $plugin_admin, 'glp_author_user_search' );
        $this->loader->add_action( 'wp_ajax_nopriv_glp_author_user_search', $plugin_admin, 'glp_author_user_search' );

        // Add Settings link to the plugin
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
        $this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

        // Add row meta link to the plugin
        $this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'add_plugin_row_meta',10 ,2 );

        // Before VC Init
        $this->loader->add_action( 'vcv:api', $plugin_admin, 'vc_before_init_actions' );

        $this->loader->add_action( 'elementor/widgets/widgets_registered', $plugin_admin, 'gpg_el_widgets_registered' );

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'codemirror_enqueue_scripts');
		
		$this->loader->add_action( 'in_admin_footer', $plugin_admin, 'gallery_admin_footer', 1 );

		// Sale Banner
        // $this->loader->add_action( 'admin_notices', $plugin_admin, 'glp_sale_baner', 1 );
        //$this->loader->add_action( 'admin_notices', $data_admin, 'glp_sale_baner', 1 );

        $this->loader->add_action( 'wp_ajax_glp_dismiss_button', $plugin_admin, 'glp_dismiss_button' );
        $this->loader->add_action( 'wp_ajax_nopriv_glp_dismiss_button', $plugin_admin, 'glp_dismiss_button' );

		// custom fields
		$this->loader->add_filter('attachment_fields_to_edit', $plugin_admin, 'add_custom_fields_to_media_edit_screen', 10, 2);
		$this->loader->add_filter('attachment_fields_to_save', $plugin_admin, 'save_custom_fields_value', 10, 2);
		

		$this->loader->add_action( 'add_attachment', $plugin_admin, 'extract_exif_data' );
	
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Glp_Gallery_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_map_public = new Glp_Map_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public_gallery_category = new Geolocated_Photo_Category( $this->get_plugin_name(), $this->get_version() );
		$plugin_public_extra_shortcodes = new Ays_Gallery_Extra_Shortcodes_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'init', $plugin_public, 'ays_initialize_gallery_shortcode');
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles_early' );
		$this->loader->add_filter( 'wp_img_tag_add_decoding_attr', $plugin_public, 'ays_gallery_wp_get_attachment_image_attributes' );

    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Geolocated_Photo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
