<?php
/**
 * Comments functions
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Settings
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Comments' ) ) {

	/**
	 * Enable or disable comments.
	 */
	class Comments {
		/**
		 * Get Options callback
		 *
		 * @var $main_plugin Callback function to get the plugin options
		 * @access private
		 */
		private $main_plugin = null;

		/**
		 * Class constructor.
		 *
		 * @param function $plugin Callback to get the plugin options.
		 */
		public function __construct( $plugin ) {
			$this->main_plugin = $plugin;

			// Disable comments, pings and remove the comments from the admin menu.
			add_action( 'admin_menu', array( $this, 'disable_comments_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'comments_admin_menu_redirect' ) );
			add_action( 'admin_init', array( $this, 'disable_comments_dashboard' ) );
			add_action( 'admin_init', array( $this, 'disable_comments_admin_bar' ) );

		}

		/**
		 * Disable comments from the admin menu
		 */
		public function disable_comments_admin_menu() {

			$options = $this->main_plugin->get_options();
			if ( '1' === $options['comments'] ) {
				remove_menu_page( 'edit-comments.php' );
				remove_submenu_page( 'options-general.php', 'options-discussion.php' );
			}

		}

		/**
		 * Redirect any calls to the Comments page.
		 *
		 * @global type $pagenow
		 */
		public function comments_admin_menu_redirect() {

			global $pagenow;

			$options = $this->main_plugin->get_options();
			if ( '1' === $options['comments'] ) {
				if ( 'edit-comments.php' === $pagenow ) {
					wp_safe_redirect( admin_url() );
					exit;
				}
			}

		}

		/**
		 * Remove comments metabox from the dashboard
		 */
		public function disable_comments_dashboard() {

			$options = $this->main_plugin->get_options();
			if ( '1' === $options['comments'] ) {
				remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
			}

		}

		/**
		 * Disable the comments in the admin bar
		 */
		public function disable_comments_admin_bar() {

			$options = $this->main_plugin->get_options();
			if ( '1' === $options['comments'] ) {
				if ( is_admin_bar_showing() ) {
					remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
				}
			}

		}

	}

}