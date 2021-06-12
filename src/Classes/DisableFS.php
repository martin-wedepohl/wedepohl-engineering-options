<?php
/**
 * Disable Blocks Full Screen
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

if ( ! class_exists( 'DisableFS' ) ) {

	/**
	 * Enable or disable comments.
	 */
	class DisableFS {

		/**
		 * Class constructor.
		 */
		public function __construct() {

			add_action( 'enqueue_block_editor_assets', array( $this, 'disable_editor_fullscreen' ) );

		}

		public function disable_editor_fullscreen() {

			$script = "window.onload = function() { const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' ); if ( isFullscreenMode ) { wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' ); } }";
			wp_add_inline_script( 'wp-blocks', $script );

		}

	}

}
