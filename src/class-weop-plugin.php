<?php
/**
 * Plugin Name: Wedepohl Engineering Options Plugin
 * Plugin URI:  https://github.com/martin-wedepohl/wedepohl-engineering-options/
 * Description: Plugin for SpyGlass HiTek or Wedepohl Engineering Websites
 * Version:     0.1.38
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

		const DEBUG_PLUGIN   = true;
		const PLUGIN_NAME    = 'weop';
		const OPTIONS_NAME   = 'weop_options';
		const PLUGIN_VERSION = '0.1.38';

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
		 * The title separators array
		 *
		 * @var array $separators Array of potential title separators
		 * @access private
		 */
		private $separators;

		/**
		 * Class constructor
		 */
		public function __construct() {

			$this->plugin       = plugin_basename( __FILE__ );
			$this->plugin_name  = self::PLUGIN_NAME;
			$this->options_name = self::OPTIONS_NAME;
			$this->separators   = array( '|', '•', '›', '»' );

			// Register the plugin hooks.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

			// Setup enqueue actions.
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

			// Add page templates.
			add_filter( 'page_template', array( $this, 'load_page_template' ) );

			// Start the session.
			add_action( 'init', array( $this, 'session_start' ) );

			// Disable WordPress sitemap.
			add_filter( 'wp_sitemaps_enabled', '__return_false' );

			// Set up for redirects
			add_action( 'template_include', array( $this, 'template_contents' ) );			
			add_filter( 'redirect_canonical', array( $this, 'prevent_slash_on_map_variable' ) );
			add_action( 'init', array( $this, 'page_rewrites' ), 0 );

		}

		/**
		 * Display the template content if the query map variable is set.
		 *
		 * map='sitemap' - Display the sitemap template.
		 *
		 * @param string $template The template string.
		 *
		 * @return string The template string if the map variable is NOT set or not valid.
		 */
		public function template_contents( $template ) {

			$map = get_query_var( 'map' );

			if ( ! empty( $map ) ) {
				if ( 'sitemap' === $map ) {
					$file_path  = plugin_dir_path( __FILE__ );
					include $file_path . 'includes/templates/sitemap-xml-template.php';
				}
			}

			return $template;

		}

		/**
		 * Prevent a slash being added to the end of the url when the map variable is set.
		 *
		 * @param string $redirect The redirection URL.
		 *
		 * @return string The redirection URL or false if the map variable is set.
		 */
		public function prevent_slash_on_map_variable( $redirect ) {

			if ( get_query_var( 'map' ) ) {
				return false;
			}

			return $redirect;

		}

		/**
		 * Rewrite the pages.
		 *
		 * sitemap.xml => index.php?map=sitemap
		 *
		 * IMPORTANT: Flush the rewrite rules if any changes are made to this function.
		 */
		public function page_rewrites() {

			global $wp;

			$wp->add_query_var( 'map' );
			add_rewrite_rule( 'sitemap\.xml$', 'index.php?map=sitemap', 'top' );

		}

		/**
		 * Start the session so we can pass the captcha on the contact us page
		 */
		public function session_start() {

			if ( ! session_id() ) {
				session_start();
			}

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
		 * Return if we are debugging the plugin
		 *
		 * @return bool If debugging plugin
		 */
		public function isDebug() : bool {
			return self::DEBUG_PLUGIN;
		}

		/**
		 * Return plugin version
		 *
		 * @return string Plugin Version
		 */
		public function version() : string {
			return self::PLUGIN_VERSION;
		}

		/**
		 * Enqueue all the required files
		 */
		public function enqueue() {

			global $template;

			$basename = basename( $template );
			$css      = '';
			$js       = '';

			if ( 'resume-template.php' === $basename || 'seminars-template.php' === $basename ) {
				$file = \plugin_dir_path( __FILE__ ) . 'dist/css/resume.min.css';
				$css  = \plugin_dir_url( __FILE__ ) . 'dist/css/resume.min.css';
			} elseif ( 'projects-template.php' === $basename ) {
				$file = \plugin_dir_path( __FILE__ ) . 'dist/css/projects.min.css';
				$css  = \plugin_dir_url( __FILE__ ) . 'dist/css/projects.min.css';
			} elseif ( 'plugins-template.php' === $basename ) {
				$file = \plugin_dir_path( __FILE__ ) . 'dist/css/plugins.min.css';
				$css  = \plugin_dir_url( __FILE__ ) . 'dist/css/plugins.min.css';
			} elseif ( 'contact-us-template.php' === $basename ) {
				$file = \plugin_dir_path( __FILE__ ) . 'dist/css/contact-us.min.css';
				$css  = \plugin_dir_url( __FILE__ ) . 'dist/css/contact-us.min.css';
			}

			if ( '' !== $css ) {
				$filemtime = filemtime( $file );
				\wp_enqueue_style(
					'weop_style',
					$css,
					array(),
					self::DEBUG_PLUGIN ? $filemtime : self::PLUGIN_VERSION,
					'all'
				);
			}

			if ( 'contact-us-template.php' === $basename ) {
				$file = \plugin_dir_path( __FILE__ ) . 'dist/js/contact-us.min.js';
				$js  = \plugin_dir_url( __FILE__ ) . 'dist/js/contact-us.min.js';
			}

			if ( '' !== $js ) {
				$filemtime = filemtime( $file );
				\wp_enqueue_script(
					'weop_script',
					$js,
					array(),
					self::DEBUG_PLUGIN ? $filemtime : self::PLUGIN_VERSION,
					true
				);
			}
		}

		/**
		 * Enqueue admin styles & scripts
		 */
		public function enqueue_admin() {

			wp_enqueue_style( 'weop_admin', plugin_dir_url( __FILE__ ) . 'dist/css/style-admin.min.css', array(), self::PLUGIN_VERSION );
			wp_enqueue_script( 'weop_admin', plugin_dir_url( __FILE__ ) . 'dist/js/script-admin.min.js', array(), self::PLUGIN_VERSION, true );

		}

		/**
		 * Set the menu and submenu pages
		 */
		public function menu_settings() {

			$options      = $this->get_options();
			$menu_postion = $options['menu_position'];
			$menu_icon    = $options['menu_icon'];

			add_menu_page(
				__( 'Wedepohl Options', 'weop' ),
				__( 'Wed Eng Options', 'weop' ),
				'manage_options',
				$this->plugin_name,
				array( $this, 'display_settings' ),
				$menu_icon,
				$menu_postion
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
<script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag("js", new Date());gtag("config","<?php echo esc_attr( $analytics_code ); ?>");</script>
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
	<h1><?php esc_html_e( 'Wedepohl Engineering Options - Version: ' . self::PLUGIN_VERSION, 'weop' ); ?></h1>
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
	<h1><?php esc_html_e( 'Wedepohl Engineering Options Information - Version: ' . self::PLUGIN_VERSION, 'weop' ); ?></h1>
	<div class="we-tabs">
		<input id="tab1" name="tabs" type="radio" checked="checked">
		<label for="tab1"><?php esc_html_e( 'Settings', 'weop' ); ?></label>
		<input id="tab2" name="tabs" type="radio">
		<label for="tab2"><?php esc_html_e( 'Custom Posts', 'weop' ); ?></label>
		<input id="tab3" name="tabs" type="radio">
		<label for="tab3"><?php esc_html_e( 'Shortcodes', 'weop' ); ?></label>
		<input id="tab4" name="tabs" type="radio">
		<label for="tab4"><?php esc_html_e( 'Filters', 'weop' ); ?></label>
		<input id="tab5" name="tabs" type="radio">
		<label for="tab5"><?php esc_html_e( 'Actions', 'weop' ); ?></label>
		<div class="tab content1">
			<ul>
				<li><strong><?php esc_html_e( 'Menu Position:', 'weop' ); ?></strong> <?php esc_html_e( 'The default menu position in the admin menu (Default=4)', 'weop' ); ?></li>
				<li><strong><?php esc_html_e( 'Menu Icon:', 'weop' ); ?></strong> <?php esc_html_e( 'The default menu icon in the admin menu (Default=dashicons-admin-generic)', 'weop' ); ?></li>
				<li><strong><?php esc_html_e( 'Google Analytics Code:', 'weop' ); ?></strong> <?php esc_html_e( 'Google Analytics Code', 'weop' ); ?></li>
				<li><strong><?php esc_html_e( 'Disable Comments:', 'weop' ); ?></strong> <?php esc_html_e( 'Enable/Disable comments in the admin menus', 'weop' ); ?></li>
				<li><strong><?php esc_html_e( 'Disable Block Full Screen:', 'weop' ); ?></strong> <?php esc_html_e( 'Enable/Disable WordPress Blocks Full Screen Editor', 'weop' ); ?></li>
			</ul>
		</div>
		<div class="tab content2">
			<ul>
				<li><strong>Activities</strong> - <?php esc_html_e( 'Activitues with start and stop dates', 'weop' ); ?> (F Y)</li>
				<li><strong>Education</strong> - <?php esc_html_e( 'Education taken including seminars', 'weop' ); ?></li>
				<li><strong>Jobs</strong> - <?php esc_html_e( 'Jobs worked at', 'weop' ); ?> (F Y)</li>
				<li><strong>Plugins</strong> - <?php esc_html_e( 'WordPress Plugins developed', 'weop' ); ?></li>
				<li><strong>Projects</strong> - <?php esc_html_e( 'Website Projects', 'weop' ); ?></li>
				<li><strong>Skills</strong> - <?php esc_html_e( 'Skills', 'weop' ); ?></li>
			</ul>
		</div>
		<div class="tab content3">
			<ul>
				<li><strong>[weop_activities date_format="F Y"]</strong> - <?php esc_html_e( 'Return all the Activities with an optional date format', 'weop' ); ?> (F Y)</li>
				<li><strong>[weop_education show_seminars="false"]</strong> - <?php esc_html_e( 'Return all the Education with an optional show_seminar (default false) if the education is just a seminar', 'weop' ); ?></li>
				<li><strong>[weop_jobs date_format="F Y"]</strong> - <?php esc_html_e( 'Return all the Jobs with an optional date format', 'weop' ); ?> (F Y)</li>
				<li><strong>[weop_plugins]</strong> - <?php esc_html_e( 'Return all the Plugins', 'weop' ); ?></li>
				<li><strong>[weop_projects]</strong> - <?php esc_html_e( 'Return all the Projects', 'weop' ); ?></li>
				<li><strong>[weop_skills]</strong> - <?php esc_html_e( 'Return all the Skills', 'weop' ); ?></li>
			</ul>
		</div>
		<div class="tab content4">
			<ul>
				<li><strong>weop_activities_query</strong> - <?php esc_html_e( 'Filter to alter the activities shortcode query', 'weop' ); ?></li>
				<li><strong>weop_activities_html</strong> - <?php esc_html_e( 'Filter to alter the activities shortcode html', 'weop' ); ?></li>
				<li><strong>weop_jobs_query</strong> - <?php esc_html_e( 'Filter to alter the jobs shortcode query', 'weop' ); ?></li>
				<li><strong>weop_jobs_html</strong> - <?php esc_html_e( 'Filter to alter the jobs shortcode html', 'weop' ); ?></li>
				<li><strong>weop_education_query</strong> - <?php esc_html_e( 'Filter to alter the education shortcode query', 'weop' ); ?></li>
				<li><strong>weop_education_html</strong> - <?php esc_html_e( 'Filter to alter the education shortcode html', 'weop' ); ?></li>
				<li><strong>weop_plugins_query</strong> - <?php esc_html_e( 'Filter to alter the plugins shortcode query', 'weop' ); ?></li>
				<li><strong>weop_plugins_html</strong> - <?php esc_html_e( 'Filter to alter the plugins shortcode html', 'weop' ); ?></li>
				<li><strong>weop_projects_query</strong> - <?php esc_html_e( 'Filter to alter the projects shortcode query', 'weop' ); ?></li>
				<li><strong>weop_projects_html</strong> - <?php esc_html_e( 'Filter to alter the projects shortcode html', 'weop' ); ?></li>
				<li><strong>weop_skills_query</strong> - <?php esc_html_e( 'Filter to alter the skills shortcode query', 'weop' ); ?></li>
				<li><strong>weop_skills_html</strong> - <?php esc_html_e( 'Filter to alter the skills shortcode html', 'weop' ); ?></li>
			</ul>
		</div>
		<div class="tab content5">
			<ul>
				<li><strong>weop_activities_before</strong> - <?php esc_html_e( 'Action before the activities shortcode', 'weop' ); ?></li>
				<li><strong>weop_activities_after</strong> - <?php esc_html_e( 'Action after the activities shortcode', 'weop' ); ?></li>
				<li><strong>weop_education_before</strong> - <?php esc_html_e( 'Action before the education shortcode', 'weop' ); ?></li>
				<li><strong>weop_education_after</strong> - <?php esc_html_e( 'Action after the education shortcode', 'weop' ); ?></li>
				<li><strong>weop_jobs_before</strong> - <?php esc_html_e( 'Action before the jobs shortcode', 'weop' ); ?></li>
				<li><strong>weop_jobs_after</strong> - <?php esc_html_e( 'Action after the jobs shortcode', 'weop' ); ?></li>
				<li><strong>weop_plugins_before</strong> - <?php esc_html_e( 'Action before the plugins shortcode', 'weop' ); ?></li>
				<li><strong>weop_plugins_after</strong> - <?php esc_html_e( 'Action after the plugins shortcode', 'weop' ); ?></li>
				<li><strong>weop_projects_before</strong> - <?php esc_html_e( 'Action before the projects shortcode', 'weop' ); ?></li>
				<li><strong>weop_projects_after</strong> - <?php esc_html_e( 'Action after the projects shortcode', 'weop' ); ?></li>
				<li><strong>weop_skills_before</strong> - <?php esc_html_e( 'Action before the skills shortcode', 'weop' ); ?></li>
				<li><strong>weop_skills_after</strong> - <?php esc_html_e( 'Action after the skills shortcode', 'weop' ); ?></li>
			</ul>
		</div>
	</div>
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
					'analytics'        => '',
					'disable_comments' => '',
					'disable_fs'       => '',
					'disable_seo'      => '',
					'menu_position'    => 4,                         // Default in between Dashboard and Posts.
					'menu_icon'        => 'dashicons-admin-generic', // Default gear.
					'title_separator'  => '',                        // Default pipe.
				),
				$options
			);

			return $options;

		}

		/**
		 * Get the separators array
		 *
		 * @return array
		 */
		public function get_separators() {

			return $this->separators;

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

			// Create menu position options array.
			$select_options = array();
			for ( $i = 0; $i <= 200; $i++ ) {
				$select_options[ $i ] = $i;
			}
			// Fill in WordPress predefined menu positions.
			$select_options[2]  = '2 - Dashboard';
			$select_options[4]  = '4 - Separator';
			$select_options[5]  = '5 - Posts';
			$select_options[10] = '10 - Media';
			$select_options[15] = '15 - Links';
			$select_options[20] = '20 - Pages';
			$select_options[25] = '25 - Comments';
			$select_options[59] = '59 - Separator';
			$select_options[60] = '60 - Appearance';
			$select_options[65] = '65 - Plugins';
			$select_options[70] = '70 - Users';
			$select_options[75] = '75 - Tools';
			$select_options[80] = '80 - Settings';
			$select_options[99] = '99 - Separator';

			add_settings_field(
				'menu_position',
				__( 'Menu Position:', 'weop' ),
				array( $settings, 'display_select_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'label-classes' => 'input-label',
					'label-text'    => __( 'Select menu position for plugin menu', 'weop' ),
					'classes'       => 'regular-text',
					'options'       => $select_options,
					'value'         => isset( $options['menu_position'] ) ? $options['menu_position'] : '',
					'name'          => "{$this->options_name}[menu_position]",
					'id'            => "{$this->options_name}[menu_position]",
				)
			);

			$dashicons = array(
				'menu',
				'admin-site',
				'dashboard',
				'admin-post',
				'admin-media',
				'admin-links',
				'admin-page',
				'admin-comments',
				'admin-appearance',
				'admin-plugins',
				'admin-users',
				'admin-tools',
				'admin-settings',
				'admin-network',
				'admin-home',
				'admin-generic',
				'admin-collapse',
				'welcome-write-blog',
				'welcome-add-page',
				'welcome-view-site',
				'welcome-widgets-menus',
				'welcome-comments',
				'welcome-learn-more',
				'format-aside',
				'format-image',
				'format-gallery',
				'format-video',
				'format-status',
				'format-quote',
				'format-chat',
				'format-audio',
				'camera',
				'images-alt',
				'images-alt2',
				'video-alt',
				'video-alt2',
				'video-alt3',
				'image-rotate',
				'image-crop',
				'image-rotate-left',
				'image-rotate-right',
				'image-flip-vertical',
				'image-flip-horizontal',
				'undo',
				'redo',
				'editor-bold',
				'editor-italic',
				'editor-ul',
				'editor-ol',
				'editor-quote',
				'editor-alignleft',
				'editor-aligncenter',
				'editor-alignright',
				'editor-insertmore',
				'editor-spellcheck',
				'editor-distractionfree',
				'editor-kitchensink',
				'editor-underline',
				'editor-justify',
				'editor-textcolor',
				'editor-paste-word',
				'editor-paste-text',
				'editor-removeformatting',
				'editor-video',
				'editor-customchar',
				'editor-outdent',
				'editor-indent',
				'editor-help',
				'editor-strikethrough',
				'editor-unlink',
				'editor-rtl',
				'align-left',
				'align-right',
				'align-center',
				'align-none',
				'lock',
				'calendar',
				'visibility',
				'post-status',
				'edit',
				'trash',
				'arrow-up',
				'arrow-down',
				'arrow-right',
				'arrow-left',
				'arrow-up-alt',
				'arrow-down-alt',
				'arrow-right-alt',
				'arrow-left-alt',
				'arrow-up-alt2',
				'arrow-down-alt2',
				'arrow-right-alt2',
				'arrow-left-alt2',
				'sort',
				'leftright',
				'list-view',
				'exerpt-view',
				'share',
				'share-alt',
				'share-alt2',
				'twitter',
				'rss',
				'facebook',
				'facebook-alt',
				'googleplus',
				'networking',
				'hammer',
				'art',
				'migrate',
				'performance',
				'wordpress',
				'wordpress-alt',
				'pressthis',
				'update',
				'screenoptions',
				'info',
				'cart',
				'feedback',
				'cloud',
				'translation',
				'tag',
				'category',
				'yes',
				'no',
				'no-alt',
				'plus',
				'minus',
				'dismiss',
				'marker',
				'star-filled',
				'star-half',
				'star-empty',
				'flag',
				'location',
				'location-alt',
				'vault',
				'shield',
				'shield-alt',
				'search',
				'slides',
				'analytics',
				'chart-pie',
				'chart-bar',
				'chart-line',
				'chart-area',
				'groups',
				'businessman',
				'id',
				'id-alt',
				'products',
				'awards',
				'forms',
				'portfolio',
				'book',
				'book-alt',
				'download',
				'upload',
				'backup',
				'lightbulb',
				'smiley',
			);

			$select_options = array();
			foreach ( $dashicons as $icon ) {
				$iconstr                    = 'dashicons-' . $icon;
				$select_options[ $iconstr ] = $icon;
			}

			$icon = isset( $options['menu_icon'] ) ? $options['menu_icon'] : '';
			add_settings_field(
				'menu_icon',
				__( 'Menu Icon:', 'weop' ),
				array( $settings, 'display_select_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'label-classes' => 'content-icon dashicons ' . $icon,
					'classes'       => 'regular-text select-icon',
					'options'       => $select_options,
					'value'         => $icon,
					'name'          => "{$this->options_name}[menu_icon]",
					'id'            => "{$this->options_name}[menu_icon]",
				)
			);

			$separator = isset( $options['title_separator'] ) ? $options['title_separator'] : '';
			add_settings_field(
				'title_separator',
				__( 'Pick the title separator:', 'weop' ),
				array( $settings, 'display_select_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'classes'       => 'regular-text',
					'options'       => $this->separators,
					'value'         => $separator,
					'name'          => "{$this->options_name}[title_separator]",
					'id'            => "{$this->options_name}[title_separator]",
				)
			);

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
				'disable_seo',
				__( 'Disable SEO:', 'weop' ),
				array( $settings, 'display_checkbox_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'label-text' => __( 'Disable SEO', 'weop' ),
					'value'      => $options['disable_seo'],
					'name'       => "{$this->options_name}[disable_seo]",
					'id'         => "{$this->options_name}[disable_seo]",
				)
			);

			add_settings_field(
				'disable_comments',
				__( 'Disable Comments:', 'weop' ),
				array( $settings, 'display_checkbox_field' ),
				$this->plugin_name,
				'options_section',
				array(
					'label-text' => __( 'Disable comments on website', 'weop' ),
					'value'      => $options['disable_comments'],
					'name'       => "{$this->options_name}[disable_comments]",
					'id'         => "{$this->options_name}[disable_comments]",
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
		 * Plugin directory: includes/templates
		 * Theme directory: plugins/wedepohl-engineering-options/templates
		 *
		 * @param string $template Template file.
		 *
		 * @return string - Template to use
		 */
		public function load_page_template( $template ) : string {

			if ( is_page( 'plugins' ) ) {
				$file        = 'templates/plugins-template.php';
				$plugin_dir  = plugin_dir_path( __FILE__ ) . 'includes/';
				$plugin_file = $plugin_dir . $file;
				$theme_dir   = get_stylesheet_directory() . '/plugins/wedepohl-engineering-options/';
				$theme_file  = $theme_dir . $file;

				if ( file_exists( $theme_file ) ) {
					return $theme_file;
				} elseif ( file_exists( $plugin_file ) ) {
					return $plugin_file;
				}
			} elseif ( is_page( 'projects' ) ) {
				$file        = 'templates/projects-template.php';
				$plugin_dir  = plugin_dir_path( __FILE__ ) . 'includes/';
				$plugin_file = $plugin_dir . $file;
				$theme_dir   = get_stylesheet_directory() . '/plugins/wedepohl-engineering-options/';
				$theme_file  = $theme_dir . $file;

				if ( file_exists( $theme_file ) ) {
					return $theme_file;
				} elseif ( file_exists( $plugin_file ) ) {
					return $plugin_file;
				}
			} elseif ( is_page( 'resume' ) ) {
				$file        = 'templates/resume-template.php';
				$plugin_dir  = plugin_dir_path( __FILE__ ) . 'includes/';
				$plugin_file = $plugin_dir . $file;
				$theme_dir   = get_stylesheet_directory() . '/plugins/wedepohl-engineering-options/';
				$theme_file  = $theme_dir . $file;

				if ( file_exists( $theme_file ) ) {
					return $theme_file;
				} elseif ( file_exists( $plugin_file ) ) {
					return $plugin_file;
				}
			} elseif ( is_page( 'additional-seminars' ) ) {
				$file        = 'templates/seminars-template.php';
				$plugin_dir  = plugin_dir_path( __FILE__ ) . 'includes/';
				$plugin_file = $plugin_dir . $file;
				$theme_dir   = get_stylesheet_directory() . '/plugins/wedepohl-engineering-options/';
				$theme_file  = $theme_dir . $file;

				if ( file_exists( $theme_file ) ) {
					return $theme_file;
				} elseif ( file_exists( $plugin_file ) ) {
					return $plugin_file;
				}
			} elseif ( is_page( 'contact-us' ) ) {
				$file        = 'templates/contact-us-template.php';
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

	$weop    = new WEOP_Plugin();
	$options = $weop->get_options();

	if ( '1' === $options['disable_comments'] ) {
		new Classes\Comments();
	}

	if ( '1' === $options['disable_fs'] ) {
		new Classes\DisableFS();
	}

	if ( '1' !== $options['disable_seo'] ) {
		new Classes\Seo( $weop );
	}

	// Require $weop_contact for the contact template page
	$weop_contact = new Classes\Contact();
	new Classes\Activities();
	new Classes\Education();
	new Classes\Jobs();
	new Classes\Plugins();
	new Classes\Projects();
	new Classes\Skills();

}
