<?php
/**
 * Plugin Name: Wedepohl Engineering Options Plugin
 * Plugin URI:  https://github.com/martin-wedepohl/wedepohl-engineering-options/
 * Description: Plugin for SpyGlass HiTek or Wedepohl Engineering Websites
 * Version:     0.1.0
 * Author:      Martin Wedepohl <martin@wedepohlengineering.com>
 * Author URI:  http://wedepohlengineering.com/
 * License:     GPL3 or higher
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain: weop
 * Domain Path: /languages
 *
 * Wedepohl Engineering Options Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Wedepohl Engineering Options Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wedepohl Engineering Options. If not, see https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * @package WEOP_Plugin
 */

namespace WEOP;

use WEOP\Classes\Settings;

require_once __DIR__ . '/vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'WEOP_Plugin' ) ) {

	/**
	 * Plugin for the SpyGlass HiTek/Wedepohl Engineering Website.
	 *
	 * @package   WEOP_Plugin
	 * @author    Martin Wedepohl <martin@wedepohlengineering.com>
	 * @copyright 2021 Wedepohl Engineering
	 */
	class WEOP_Plugin {

		const PLUGIN_NAME    = 'weop';
		const OPTIONS_NAME   = 'weop_options';
		const PLUGIN_VERSION = '0.1.0';

		/**
		 * Plugin name
		 *
		 * @var $plugin string Contains the Plugin name
		 * @access private
		 */
		private $plugin;

		/**
		 * Database options name
		 *
		 * @var string $options_name string name of the options in the database
		 * @access private
		 */
		private $options_name;

		/**
		 * Called when the plugin is uninstalled
		 */
		public static function weop_uninstall() {

			delete_option( self::OPTIONS_NAME );

		}

		/**
		 * Class constructor
		 */
		public function __construct() {

			$this->plugin       = plugin_basename( __FILE__ );
			$this->plugin_name  = self::PLUGIN_NAME;
			$this->options_name = self::OPTIONS_NAME;

			// Register the plugin hooks.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
			register_uninstall_hook( __FILE__, array( 'WEOP_Plugin', 'weop_uninstall' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );

			// Set up the menu links.
			add_action( 'admin_menu', array( $this, 'menu_settings' ) );
			add_action( 'admin_init', array( $this, 'init_settings' ) );
			add_filter( "plugin_action_links_{$this->plugin}", array( $this, 'add_settings_link' ) );

			// Reorder menu links.
			add_filter( 'custom_menu_order', array( $this, 'reorder_admin_menu' ) );

			// Add Google Analytics to head.
			add_action( 'wp_head', array( $this, 'add_analytics_in_header' ), 0 );

		}

		/**
		 * Called on plugin activation.
		 */
		public function activation() {

			flush_rewrite_rules();

		}

		/**
		 * Called on plugin deactivation.
		 */
		public function deactivation() {

			flush_rewrite_rules();

		}

		/**
		 * Enqueue all the required files
		 */
		public function enqueue() {

			wp_enqueue_style( 'weop_css', plugin_dir_url( __FILE__ ) . 'dist/css/style.min.css', array(), '0.1.0' );

		}

		/**
		 * Enqueue admin styles & scripts
		 */
		public function enqueue_admin() {

			wp_enqueue_style( 'weop_admin_css', plugin_dir_url( __FILE__ ) . 'dist/css/style-admin.min.css', array(), '0.1.0' );
			wp_enqueue_script( 'weop_js', plugin_dir_url( __FILE__ ) . 'dist/js/script.min.js', array(), '0.1.0', true );

		}

		/**
		 * Set the menu and submenu pages
		 */
		public function menu_settings() {

			add_menu_page(
				__( 'Wedepohl Options', 'weop' ),
				__( 'Wed Eng Options', 'weop' ),
				'manage_options',
				$this->plugin_name,
				array( $this, 'display_settings' ),
				'dashicons-admin-generic',
				4
			);

			add_submenu_page(
				$this->plugin_name,
				__( 'Wedepohl Engineering Settings', 'weop' ),
				__( 'Settings', 'weop' ),
				'manage_options',
				"{$this->plugin_name}",
				array( $this, 'display_settings' )
			);
			add_submenu_page(
				$this->plugin_name,
				__( 'Wedepohl Engineering Information', 'weop' ),
				__( 'Information', 'weop' ),
				'manage_options',
				"{$this->plugin_name}-info",
				array( $this, 'display_info' )
			);

		}

		/**
		 * Re-order the custom menu
		 *
		 * @global array $submenu
		 * @param boolean $menu_order The menu order.
		 * @return boolean
		 */
		public function reorder_admin_menu( $menu_order ) {

			global $submenu;

			$wedengmenu = $submenu['weop'];

			$newmenu   = array();
			$key1      = array_search( 'Settings', array_column( $wedengmenu, 0 ), true );
			$newmenu[] = $wedengmenu[ $key1 ];
			$key2      = array_search( 'Information', array_column( $wedengmenu, 0 ), true );
			$newmenu[] = $wedengmenu[ $key2 ];
			unset( $wedengmenu[ $key1 ] );
			unset( $wedengmenu[ $key2 ] );
			foreach ( $wedengmenu as $key => $menu ) {
				$newmenu[] = $menu;
			}

			$submenu['weop'] = $newmenu;

			return $menu_order;

		}

		/**
		 * Add the Google Analytics Script into the header
		 */
		public function add_analytics_in_header() {

			$options        = get_option( $this->options_name );
			$analytics_code = is_array( $options ) ? ( isset( $options['analytics'] ) ? $options['analytics'] : '' ) : '';
			if ( '' !== $analytics_code ) {
				?>
				<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $analytics_code ); ?>"></script>
					<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag("js", new Date());gtag("config","<?php echo esc_attr( $analytics_code ); ?>");
				</script>
				<?php
			}

		}

		/**
		 * Display the Wedepohl Engineering Options Page
		 */
		public function display_settings() {
			?>

			<div class="wrap">
			<?php settings_errors(); ?>
				<h1><?php esc_html_e( 'Wedepohl Engineering Options', 'weop' ); ?></h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( $this->options_name );
					do_settings_sections( $this->plugin_name );
					submit_button();
					?>
				</form>
			</div>

			<?php
		}

		/**
		 * Display the Wedepohl Engineeering Information Page
		 */
		public function display_info() {
			?>

<div class="wrap">
	<h1><?php esc_html_e( 'Wedepohl Engineering Options Information', 'weop' ); ?></h1>
	<ul>
		<?php
			$html = sprintf(
				'<strong>%s</strong> %s',
				esc_html__( 'Google Analytics Code:', 'weop' ),
				esc_html__( 'Code for Google Analytics', 'weop' )
			);
		?>
		<li><?php echo $html; ?>
		<?php
			$html = sprintf(
				'<strong>%s</strong> %s',
				esc_html__( 'Enable Comments:', 'weop' ),
				esc_html__( 'Enable/Disable comments in the admin menus', 'weop' )
			);
		?>
		<li><?php echo $html; ?>
		<?php
			$html = sprintf(
				'<strong>%s</strong> %s',
				esc_html__( 'Disable Block Full Screen:', 'weop' ),
				esc_html__( 'Enable/Disable WordPress Blocks Full Screen Editor', 'weop' )
			);
		?>
		<li><?php echo $html; ?>
	</ul>
	<h2><?php esc_html_e( 'Shortcodes', 'weop' ); ?></h2>
	<ul>
		<li>[weop_jobs] - Return all the Jobs custom post types</li>
		<li>[weop_education show_seminars=true] - Return all the Education with an optional show_seminar (default false) if the education is just a seminar</li>
		<li>[weop_activities] - Return all the Activities</li>
	</ul>
	<h2><?php esc_html_e( 'Remove Shortcode', 'weop' ); ?></h2>
	<pre>
$shortcode_handler = apply_filter( 'get_weop_shortcode_instance', NULL );
if( is_a( $shortcode_handler, 'weop_Shortcodes' ) {
	// Do something with the instance of the handler
}
	</pre>
</div>

			<?php
		}

		/**
		 * Get the options for this plugin and set to default if not present
		 *
		 * @return array
		 */
		public function get_options() {

			$options = get_option( $this->options_name );

			$options = shortcode_atts(
				array(
					'analytics'  => '',
					'comments'   => '',
					'disable_fs' => '',
				),
				$options
			);

			return $options;

		}

		/**
		 * Initialize the settings options.
		 */
		public function init_settings() {

			register_setting( $this->options_name, $this->options_name );

			$options = $this->get_options();

			add_settings_section(
				'options_section',
				'',
				'',
				$this->plugin_name
			);

			$settings = new Settings();

			add_settings_field(
				'analytics',
				__( 'Google Analytics Code:', 'weop' ),
				array( $settings, 'display_text_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'label-classes' => 'input-label',
					'label-text'    => __( 'Google Analytics Code (UA-XXXXXXXXX-X)', 'weop' ),
					'classes'       => 'regular-text',
					'value'         => isset( $options['analytics'] ) ? $options['analytics'] : '',
					'name'          => "{$this->options_name}[analytics]",
					'id'            => "{$this->options_name}[analytics]",
				)
			);

			add_settings_field(
				'comments',
				__( 'Enable Comments:', 'weop' ),
				array( $settings, 'display_checkbox_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'label-text' => __( 'Enable comments on website', 'weop' ),
					'value'      => $options['comments'],
					'name'       => "{$this->options_name}[comments]",
					'id'         => "{$this->options_name}[comments]",
				)
			);

			add_settings_field(
				'disable_fs',
				__( 'Disable Block Full Screen:', 'weop' ),
				array( $settings, 'display_checkbox_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'label-text' => __( 'Disable Block Full Screen Editor', 'weop' ),
					'value'      => $options['disable_fs'],
					'name'       => "{$this->options_name}[disable_fs]",
					'id'         => "{$this->options_name}[disable_fs]",
				)
			);

		}

		/**
		 * Create the settings links on the plugin page
		 *
		 * @param array $links Array of links.
		 * @return array Modified array of links.
		 */
		public function add_settings_link( $links ) {

			$settings_link = '<a href="admin.php?page=' . $this->plugin_name . '">' . __( 'Settings', 'weop' ) . '</a>';
			$info_link     = '<a href="admin.php?page=' . $this->plugin_name . '-info">' . __( 'Information', 'weop' ) . '</a>';
			array_push( $links, $settings_link );
			array_push( $links, $info_link );

			return $links;

		}

		/**
		 * Load the template for different files.
		 * Can be overriden by a file in the theme directory
		 *
		 * Plugin directory: templates
		 * Theme directory: plugins/wedepohl-engineering-options/templates
		 *
		 * @global array $post
		 *
		 * @return string - Template to use
		 */
		public function load_resume_template() {

			global $post;

			if ( is_page( 'resume' ) ) {
				$file        = 'templates/resume-template.php1';
				$plugin_dir  = plugin_dir_path( __FILE__ ) . 'includes/';
				$plugin_file = $plugin_dir . $file;
				$theme_dir   = get_stylesheet_directory() . '/plugins/wedepohl-engineering-options/';
				$theme_file  = $theme_dir . $file;

				if ( file_exists( $theme_file ) ) {
					return $theme_file;
				} elseif ( file_exists( $plugin_file ) ) {
					return $plugin_file;
				}
			}

			return $template;

		}

	}

	$weop = new WEOP_Plugin();
	new Classes\Jobs();
	new Classes\Comments( $weop );
	new Classes\DisableFS( $weop );

}
