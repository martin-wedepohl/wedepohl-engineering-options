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
		 * Class constructor.
		 *
		 * @param function $plugin Callback to get the plugin options.
		 */
		public function __construct( $plugin ) {

			$options = $plugin->get_options();

			if  ( '1' === $options['comments'] ) {
				// Disable comments, pings and remove the comments from the admin menu.
				add_action( 'admin_menu', array( $this, 'disable_comments_admin_menu' ) );
				add_action( 'admin_init', array( $this, 'comments_admin_menu_redirect' ) );
				add_action( 'admin_init', array( $this, 'disable_comments_dashboard' ) );
				add_action( 'admin_init', array( $this, 'disable_comments_admin_bar' ) );
				add_action( 'enqueue_block_editor_assets', array( $this, 'remove_block_discussions' ) );
			}

		}

		/**
		 * Disable comments from the admin menu
		 */
		public function disable_comments_admin_menu() {

			remove_menu_page( 'edit-comments.php' );
			remove_submenu_page( 'options-general.php', 'options-discussion.php' );

		}

		/**
		 * Redirect any calls to the Comments page.
		 *
		 * @global type $pagenow
		 */
		public function comments_admin_menu_redirect() {

			global $pagenow;

			if ( 'edit-comments.php' === $pagenow ) {
				wp_safe_redirect( admin_url() );
				exit;
			}

		}

		/**
		 * Remove comments metabox from the dashboard
		 */
		public function disable_comments_dashboard() {

			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

		}

		/**
		 * Disable the comments in the admin bar
		 */
		public function disable_comments_admin_bar() {

			if ( is_admin_bar_showing() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
			}

		}

		/**
		 * Remove the discussion meta box in block editor
		 */
		public function remove_block_discussions() {

			$script = "wp.domReady( () => { const { removeEditorPanel } = wp.data.dispatch('core/edit-post'); removeEditorPanel( 'discussion-panel' ); } );";
			wp_add_inline_script( 'wp-blocks', $script );

		}

	}

}