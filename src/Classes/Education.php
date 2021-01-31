<?php
/**
 * Education custom post type
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Education
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

use WEOP\WEOP_Plugin;
use WEOP\Classes\Settings;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Education' ) ) {

	/**
	 * Education custom post type
	 */
	class Education {

		const POST_TYPE      = 'education';
		const META_BOX_DATA  = 'weop_education_save_meta_box_data';
		const META_BOX_NONCE = 'weo_education_meta_box_nonce';

		private $max_year = 0;

		/**
		 * Return the meta key
		 */
		public static function get_meta_key() {
			return array(
				'year'        => '_meta_education_year',
				'course'      => '_meta_education_couse',
				'course_url'  => '_meta_education_course_url',
				'institution' => '_meta_education_institution',
				'seminar'     => '_meta_education_seminar',
			);
		}

		/**
		 * Return the post type
		 */
		public static function get_post_type() {
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
		public static function get_data( $post_id ) {

			$meta_key_array = self::get_meta_key();

			$year        = get_post_meta( $post_id, $meta_key_array['year'], true );
			$course      = get_post_meta( $post_id, $meta_key_array['course'], true );
			$course_url  = get_post_meta( $post_id, $meta_key_array['course_url'], true );
			$institution = get_post_meta( $post_id, $meta_key_array['institution'], true );
			$seminar     = get_post_meta( $post_id, $meta_key_array['seminar'], true );

			$data = array(
				'year'        => sanitize_text_field( $year ),
				'course'      => sanitize_text_field( $course ),
				'course_url'  => esc_url( $course_url ),
				'institution' => sanitize_text_field( $institution ),
				'seminar'     => sanitize_text_field( $seminar ),
			);

			return $data;
		}

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->max_year = date('Y');
			add_action( 'init', array( $this, 'register' ) );
			add_action( 'save_post', array( $this, 'save_meta' ), 1, 2 );
			add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'table_head' ) );
			add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'table_content' ), 10, 2 );
			add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'table_sort' ) );
			add_action( 'pre_get_posts', array( $this, 'custom_orderby' ) );
		}

		/**
		 * Register the post type
		 */
		public function register() {
			$labels = array(
				'name'               => __( 'Education', 'weop' ),
				'singular_name'      => __( 'Education', 'weop' ),
				'menu_name'          => __( 'Education', 'weop' ),
				'parent_item_colon'  => __( 'Parent Education', 'weop' ),
				'all_items'          => __( 'All Education', 'weop' ),
				'view_item'          => __( 'View Education', 'weop' ),
				'add_new_item'       => __( 'Add New Education', 'weop' ),
				'add_new'            => __( 'Add New', 'weop' ),
				'edit_item'          => __( 'Edit Education', 'weop' ),
				'update_item'        => __( 'Update Education', 'weop' ),
				'search_items'       => __( 'Search Education', 'weop' ),
				'not_found'          => __( 'Education Not Found', 'weop' ),
				'not_found_in_trash' => __( 'Education Not Found in Trash', 'weop' ),
			);

			$args = array(
				'label'                => __( 'education', 'weop' ),
				'description'          => __( 'Education', 'weop' ),
				'labels'               => $labels,
				'supports'             => array( 'title' ),
				'hierarchical'         => false,
				'public'               => true,
				'show_ui'              => true,
				'show_in_menu'         => WEOP_Plugin::PLUGIN_NAME,
				'show_in_nav_menus'    => true,
				'show_in_admin_bar'    => true,
				'menu_position'        => 5,
				'can_export'           => true,
				'has_archive'          => true,
				'exclude_from_search'  => false,
				'publicly_queryable'   => true,
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
				__( 'Education Information', 'weop' ),
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
				'label-text'    => __( 'Course URL', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['course_url'] ) ? $data['course_url'] : '',
				'name'          => 'course_url',
				'id'            => 'course_url',
			);
			$settings->display_text_field( $args );

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'Institution', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['institution'] ) ? $data['institution'] : '',
				'name'          => 'institution',
				'id'            => 'institution',
			);
			$settings->display_text_field( $args );

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'Year', 'weop' ),
				'classes'       => 'width-100',
				'value'         => $data['year'],
				'name'          => 'year',
				'type'          => 'number',
				'min'           => 1985,
				'max'           => $this->max_year,
				'step'          => 1,
				'id'            => 'year',
			);
			$settings->display_text_field( $args );

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'Seminar?', 'weop' ),
				'classes'       => 'width-100',
				'value'         => $data['seminar'],
				'name'          => 'seminar',
				'id'            => 'seminar',
			);
			$settings->display_checkbox_field( $args );

		}

		/**
		 * Save the meta box data
		 *
		 * @param int   $post_id The post ID.
		 * @param array $post    The post.
		 *
		 * @return int The post ID.
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
			$data = sanitize_text_field( $_POST['year'] );
			update_post_meta( $post_id, $meta_key_array['year'], $data );
			$data = esc_url_raw( $_POST['course_url'], array( 'http', 'https' ) );
			update_post_meta( $post_id, $meta_key_array['course_url'], $data );
			$data = sanitize_text_field( $_POST['institution'] );
			update_post_meta( $post_id, $meta_key_array['institution'], $data );
			$data = sanitize_text_field( $_POST['seminar'] );
			update_post_meta( $post_id, $meta_key_array['seminar'], $data );
		}

		/**
		 * Display the table headers for custom columns in our order
		 *
		 * @param array $columns Array of headers.
		 *
		 * @return array Modified array of headers.
		 */
		public function table_head( $columns ) {
			$newcols = array();
			// Want the selection box and title (name for our custom post type) first.
			$newcols['cb'] = $columns['cb'];
			unset( $columns['cb'] );
			$newcols['title'] = __( 'Course', 'weop' );
			unset( $columns['title'] );
			// Our custom meta data columns.
			$newcols['course_url']  = __( 'Course URL', 'weop' );
			$newcols['institution'] = __( 'Institution', 'weop' );
			$newcols['year']        = __( 'Year', 'weop' );
			$newcols['seminar']     = __( 'Is Seminar', 'weop' );
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

			if ( 'year' === $column_name ) {
				echo esc_attr( $data['year'] );
			} elseif ( 'course_url' === $column_name ) {
				if ( isset( $data['course_url'] ) ) {
					echo '<a href="' . esc_url( $data['course_url'] ) .
					'" target="_blank" title="Go To Course">' .
					esc_url( $data['course_url'] ) . '</a>';
				}
			} elseif ( 'institution' === $column_name ) {
				echo esc_attr( $data['institution'] );
			} elseif ( 'seminar' === $column_name ) {
				if ( '1' === $data['seminar'] ) {
					echo 'YES';
				}
			}
		}

		/**
		 * Sort the custom post type by meta data
		 */
		public function table_sort( $columns ) {
			$columns['year'] = 'year';
			$columns['institution'] = 'institution';

			return $columns;
		}

		/**
		 * Custom order by function
		 *
		 * @param object $query The WordPress database query object
		 */
		function custom_orderby( $query ) {
			if ( ! is_admin() )
			  return;
		  
			$meta_key_array = self::get_meta_key();
			$orderby = $query->get( 'orderby');

			switch( $orderby ) {
				case 'year':
					$query->set( 'meta_key', $meta_key_array[ $orderby ] );
					$query->set( 'orderby', 'meta_value_num' );
					break;
				case 'institution':
					$query->set( 'meta_key', $meta_key_array[ $orderby ] );
					$query->set( 'orderby', 'meta_value' );
				break;
				default:
					break;
			}
		}

	}

}
