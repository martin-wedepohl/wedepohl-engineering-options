<?php
/**
 * Plugins custom post type
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Plugins
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

use WEOP\WEOP_Plugin;
use WEOP\Classes\Settings;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Plugins' ) ) {

	/**
	 * Projects custom post type
	 */
	class Plugins {

		const POST_TYPE      = 'weop_plugins';
		const META_BOX_DATA  = 'weop_plugins_save_meta_box_data';
		const META_BOX_NONCE = 'weop_plugins_meta_box_nonce';

		/**
		 * Return the meta key
		 *
		 * @return array The array of meta keys
		 */
		public static function get_meta_key() : array {
			return array(
				'plugin_url' => '_meta_weop_plugin_url',
				'github_url' => '_meta_weop_github_url',
			);
		}

		/**
		 * Return the post type
		 *
		 * @return string The post type
		 */
		public static function get_post_type() : string {
			return self::POST_TYPE;
		}

		/**
		 * Get all the job meta data for the post id
		 *
		 * All the data will be sanitized.
		 *
		 * @param int $post_id The ID of the post.
		 *
		 * @return array Associative array with all the meta data
		 */
		public static function get_data( $post_id ) : array {

			$meta_key_array = self::get_meta_key();

			$plugin_url = get_post_meta( $post_id, $meta_key_array['plugin_url'], true );
			$github_url = get_post_meta( $post_id, $meta_key_array['github_url'], true );

			$data = array(
				'plugin'     => get_the_title( $post_id ),
				'plugin_url' => esc_url( $plugin_url ),
				'github_url' => esc_url( $github_url ),
			);

			return $data;
		}

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register' ) );
			add_action( 'save_post', array( $this, 'save_meta' ), 1, 2 );
			add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'table_head' ) );
			add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'table_content' ), 10, 2 );
			add_shortcode( 'weop_plugins', array( $this, 'get_plugins' ) );
		}

		/**
		 * Register the post type
		 */
		public function register() {
			$labels = array(
				'name'               => __( 'Plugins', 'weop' ),
				'singular_name'      => __( 'Plugin', 'weop' ),
				'menu_name'          => __( 'Plugins', 'weop' ),
				'parent_item_colon'  => __( 'Parent Plugin', 'weop' ),
				'all_items'          => __( 'All Plugins', 'weop' ),
				'view_item'          => __( 'View Plugin', 'weop' ),
				'add_new_item'       => __( 'Add New Plugin', 'weop' ),
				'add_new'            => __( 'Add New', 'weop' ),
				'edit_item'          => __( 'Edit Plugin', 'weop' ),
				'update_item'        => __( 'Update Plugin', 'weop' ),
				'search_items'       => __( 'Search Plugins', 'weop' ),
				'not_found'          => __( 'Plugin Not Found', 'weop' ),
				'not_found_in_trash' => __( 'Plugin Not Found in Trash', 'weop' ),
			);

			$args = array(
				'label'                => __( 'Plugins', 'weop' ),
				'description'          => __( 'Plugin', 'weop' ),
				'labels'               => $labels,
				'supports'             => array( 'title', 'editor', 'thumbnail' ),
				'hierarchical'         => false,
				'public'               => true,
				'show_ui'              => true,
				'show_in_menu'         => WEOP_Plugin::PLUGIN_NAME,
				'show_in_nav_menus'    => true,
				'show_in_admin_bar'    => true,
				'menu_position'        => 5,
				'can_export'           => false,
				'has_archive'          => false,
				'exclude_from_search'  => true,
				'publicly_queryable'   => false,
				'capability_type'      => 'post',
				'show_in_rest'         => true,
				'register_meta_box_cb' => array( $this, 'register_meta_box' ),

			);

			register_post_type( self::POST_TYPE, $args );

		}

		/**
		 * Add the meta box to the custom post type
		 */
		public function register_meta_box() {
			add_meta_box(
				'information_section',
				__( 'Plugin Information', 'weop' ),
				array( $this, 'meta_box' ),
				self::POST_TYPE,
				'side',
				'high'
			);
		}

		/**
		 * Display the meta box
		 *
		 * @global type $post - The current post
		 */
		public function meta_box() {
			global $post;
			// Nonce field to validate form request from current site.
			wp_nonce_field( self::META_BOX_DATA, self::META_BOX_NONCE );

			// Get all the meta data.
			$data = self::get_data( $post->ID );

			$settings = new Settings();

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'Plugin URL', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['plugin_url'] ) ? $data['plugin_url'] : '',
				'name'          => 'plugin_url',
				'id'            => 'plugin_url',
			);
			$settings->display_text_field( $args );

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'GitHub URL', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['github_url'] ) ? $data['github_url'] : '',
				'name'          => 'github_url',
				'id'            => 'github_url',
			);
			$settings->display_text_field( $args );

		}

		/**
		 * Save the meta box data
		 *
		 * @param int   $post_id The post ID.
		 * @param array $post    The post.
		 */
		public function save_meta( $post_id, $post ) {
			// Checks save status.
			$is_autosave    = wp_is_post_autosave( $post_id );
			$is_revision    = wp_is_post_revision( $post_id );
			$is_valid_nonce = ( isset( $_POST[ self::META_BOX_NONCE ] ) && wp_verify_nonce( $_POST[ self::META_BOX_NONCE ], self::META_BOX_DATA ) ) ? true : false;
			$can_edit       = current_user_can( 'edit_post', $post_id );
			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce || ! $can_edit ) {
				return;
			}
			// Now that we're authenticated, time to save the data.
			$meta_key_array = self::get_meta_key();
			$data           = esc_url_raw( $_POST['plugin_url'], array( 'http', 'https' ) );
			update_post_meta( $post_id, $meta_key_array['plugin_url'], $data );
			$data           = esc_url_raw( $_POST['github_url'], array( 'http', 'https' ) );
			update_post_meta( $post_id, $meta_key_array['github_url'], $data );
		}

		/**
		 * Display the table headers for custom columns in our order.
		 *
		 * @param array $columns Array of headers.
		 *
		 * @return array Modified array of headers.
		 */
		public function table_head( $columns ) : array {
			$newcols = array();
			// Want the selection box and title (name for our custom post type) first.
			$newcols['cb'] = $columns['cb'];
			unset( $columns['cb'] );
			$newcols['title'] = 'Plugin';
			unset( $columns['title'] );
			// Want date last.
			unset( $columns['date'] );
			// Add all other selected columns.
			foreach ( $columns as $col => $title ) {
				$newcols[ $col ] = $title;
			}
			// Add the date back.
			$newcols['date'] = 'Date';
			return $newcols;
		}

		/**
		 * Display the meta data associated with a post on the administration table
		 *
		 * @param string $column_name The header of the column.
		 * @param int    $post_id     The ID of the post being displayed.
		 */
		public function table_content( $column_name, $post_id ) {
			$data = self::get_data( $post_id );

			if ( 'title' === $column_name ) {
				if ( isset( $data['plugin_url'] ) ) {
					echo '<a href="' . esc_url( $data['plugin_url'] ) .
					'" target="_blank" title="Go To ' .
					esc_attr( $data['plugin'] ) . '">' .
					esc_attr( $data['plugin'] ) . '</a>';
				} else {
					echo esc_attr( $data['plugin'] );
				}
			}
		}

		/**
		 * Shortcode to return all the projects
		 *
		 * [weop_plugin]
		 *
		 * @return string The HTML string of all the jobs
		 */
		public function get_plugins() : string {

			$meta_key_array = self::get_meta_key();

			$args = array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'ASC',
			);

			$loop = new \WP_Query( apply_filters( 'weop_plugin_query', $args ) );

			$html = '';
			if ( $loop->have_posts() ) {
				while ( $loop->have_posts() ) {
					$loop->the_post();
					$post  = $loop->post;
					$data  = self::get_data( $post->ID );

					do_action( 'weop_plugins_before' );
					$html .= '<div class="plugin" id="plugin-' . $post->ID . '">';
					if ( '' === $data['plugin_url'] ) {
						$html .= '<span class="plugin-url"><span>' . $data['plugin'] . '</span>';
						$html .= '<span class="plugin-thumbnail">' . get_the_post_thumbnail( $post->ID ) . '</span>';
					} else {
						$html .= '<span class="plugin-url"><span><a href="' . $data['plugin_url'] . '" title="Click to view plugin" target="_blank">' . $data['plugin'] . '</a></span>';
						$html .= '<span class="plugin-thumbnail"><a href="' . $data['plugin_url'] . '" title="Click to view plugin" target="_blank">' . get_the_post_thumbnail( $post->ID ) . '</a></span>';
					}
					if ( '' !== $data['github_url'] ) {
						$html .= '<span class="github-url"><a href="' . $data['github_url'] . '" title="Click to view plugin GitHub Repository" target="_blank">View GitHub Repository</a></span>';
					}
					$html .= '<span class="plugin-content">' . get_the_content( $post->ID ) . '</span>';
					$html .= '<div class="plugin-divider"></div>';
					$html .= '</div>';
					do_action( 'weop_plugins_after' );
				}
			}
			\wp_reset_postdata();

			return apply_filters( 'weop_plugins_html', $html );
		}

	}
}
