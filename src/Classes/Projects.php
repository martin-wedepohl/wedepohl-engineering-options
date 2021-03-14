<?php
/**
 * Projects custom post type
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Projects
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

use WEOP\WEOP_Plugin;
use WEOP\Classes\Settings;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Projects' ) ) {

	/**
	 * Projects custom post type
	 */
	class Projects {

		const POST_TYPE      = 'weop_projects';
		const META_BOX_DATA  = 'weop_projects_save_meta_box_data';
		const META_BOX_NONCE = 'weop_projects_meta_box_nonce';

		/**
		 * Return the meta key
		 *
		 * @return array The array of meta keys
		 */
		public static function get_meta_key() : array {
			return array(
				'project_url' => '_meta_weop_project_url',
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

			$project_url = get_post_meta( $post_id, $meta_key_array['project_url'], true );

			$data = array(
				'project'     => get_the_title( $post_id ),
				'project_url' => esc_url( $project_url ),
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
			add_shortcode( 'weop_projects', array( $this, 'get_projects' ) );
		}

		/**
		 * Register the post type
		 */
		public function register() {
			$labels = array(
				'name'               => __( 'Projects', 'weop' ),
				'singular_name'      => __( 'Project', 'weop' ),
				'menu_name'          => __( 'Projects', 'weop' ),
				'parent_item_colon'  => __( 'Parent Project', 'weop' ),
				'all_items'          => __( 'All Projects', 'weop' ),
				'view_item'          => __( 'View Project', 'weop' ),
				'add_new_item'       => __( 'Add New Project', 'weop' ),
				'add_new'            => __( 'Add New', 'weop' ),
				'edit_item'          => __( 'Edit Project', 'weop' ),
				'update_item'        => __( 'Update Project', 'weop' ),
				'search_items'       => __( 'Search Projects', 'weop' ),
				'not_found'          => __( 'Project Not Found', 'weop' ),
				'not_found_in_trash' => __( 'Project Not Found in Trash', 'weop' ),
			);

			$args = array(
				'label'                => __( 'Projects', 'weop' ),
				'description'          => __( 'Project', 'weop' ),
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
				__( 'Project Information', 'weop' ),
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
				'label-text'    => __( 'Project URL', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['project_url'] ) ? $data['project_url'] : '',
				'name'          => 'project_url',
				'id'            => 'project_url',
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
			$data           = esc_url_raw( $_POST['project_url'], array( 'http', 'https' ) );
			update_post_meta( $post_id, $meta_key_array['project_url'], $data );
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
			$newcols['title'] = 'Project';
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
				if ( isset( $data['project_url'] ) ) {
					echo '<a href="' . esc_url( $data['project_url'] ) .
					'" target="_blank" title="Go To ' .
					esc_attr( $data['project'] ) . '">' .
					esc_attr( $data['project'] ) . '</a>';
				} else {
					echo esc_attr( $data['project'] );
				}
			}
		}

		/**
		 * Shortcode to return all the projects
		 *
		 * [weop_projects]
		 *
		 * @return string The HTML string of all the jobs
		 */
		public function get_projects() : string {

			$meta_key_array = self::get_meta_key();

			$args = array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			);

			$loop = new \WP_Query( apply_filters( 'weop_projects_query', $args ) );

			$html = '';
			if ( $loop->have_posts() ) {
				while ( $loop->have_posts() ) {
					$loop->the_post();
					$post = $loop->post;
					$data = self::get_data( $post->ID );

					do_action( 'weop_projects_before' );
					$html .= '<div class="project" id="project-' . $post->ID . '">';
					if ( '' === $data['project_url'] ) {
						$html .= '<span class="project-url">' . $data['project'] . '</span>';
						if ( has_post_thumbnail( $post->ID ) ) {
							$html .= '<span class="project-thumbnail">' . get_the_post_thumbnail( $post->ID ) . '</span>';
						}
					} else {
						$html .= '<span class="project-url"><a href="' . $data['project_url'] . '" title="Click to view project" target="_blank">' . $data['project'] . '</a></span>';
						if ( has_post_thumbnail( $post->ID ) ) {
							$html .= '<span class="project-thumbnail"><a href="' . $data['project_url'] . '" title="Click to view project" target="_blank">' . get_the_post_thumbnail( $post->ID ) . '</a></span>';
						}
					}
					$html .= '<span class="project-content">' . get_the_content( $post->ID ) . '</span>';
					$html .= '<div class="project-divider"></div>';
					$html .= '</div>';
					do_action( 'weop_projects_after' );
				}
			}
			\wp_reset_postdata();

			return apply_filters( 'weop_projects_html', $html );
		}

	}
}
