<?php
/**
 * Jobs custom post type
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Jobs
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

use WEOP\WEOP_Plugin;
use WEOP\Classes\Config;
use WEOP\Classes\Settings;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Jobs' ) ) {

	/**
	 * Jobs custom post type
	 */
	class Jobs {

		const MAX_DATE       = '9999-12-31';
		const POST_TYPE      = 'jobs';
		const META_BOX_DATA  = 'weop_jobs_save_meta_box_data';
		const META_BOX_NONCE = 'weo_jobs_meta_box_nonce';

		/**
		 * Return the meta key
		 */
		public static function get_meta_key() {
			return array(
				'start'       => '_meta_jobs_start',
				'end'         => '_meta_jobs_end',
				'company'     => '_meta_jobs_company',
				'company_url' => '_meta_jobs_company_url',
				'location'    => '_meta_jobs_location',
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

			$start       = get_post_meta( $post_id, '_meta_jobs_start', true );
			$end         = get_post_meta( $post_id, '_meta_jobs_end', true );
			$company     = get_post_meta( $post_id, '_meta_jobs_company', true );
			$company_url = get_post_meta( $post_id, '_meta_jobs_company_url', true );
			$location    = get_post_meta( $post_id, '_meta_jobs_location', true );

			$data = array(
				'start'       => sanitize_text_field( $start ),
				'end'         => sanitize_text_field( $end ),
				'company'     => sanitize_text_field( $company ),
				'company_url' => esc_url( $company_url ),
				'location'    => sanitize_text_field( $location ),
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
			add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'table_sort' ) );
			add_action( 'pre_get_posts', array( $this, 'custom_orderby' ) );
		}

		/**
		 * Register the post type
		 */
		public function register() {
			$labels = array(
				'name'               => __( 'Jobs', 'weop' ),
				'singular_name'      => __( 'Job', 'weop' ),
				'menu_name'          => __( 'Jobs', 'weop' ),
				'parent_item_colon'  => __( 'Parent Job', 'weop' ),
				'all_items'          => __( 'All Jobs', 'weop' ),
				'view_item'          => __( 'View Job', 'weop' ),
				'add_new_item'       => __( 'Add New Job', 'weop' ),
				'add_new'            => __( 'Add New', 'weop' ),
				'edit_item'          => __( 'Edit Job', 'weop' ),
				'update_item'        => __( 'Update Job', 'weop' ),
				'search_items'       => __( 'Search Jobs', 'weop' ),
				'not_found'          => __( 'Job Not Found', 'weop' ),
				'not_found_in_trash' => __( 'Job Not Found in Trash', 'weop' ),
			);

			$args = array(
				'label'                => __( 'jobs', 'weop' ),
				'description'          => __( 'Jobs', 'weop' ),
				'labels'               => $labels,
				'supports'             => array( 'title', 'editor' ),
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
				__( 'Job Information', 'weop' ),
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
				'label-text'    => __( 'Start Date', 'weop' ),
				'required'      => true,
				'classes'       => 'width-100',
				'value'         => isset( $data['start'] ) ? $data['start'] : '',
				'name'          => 'start',
				'type'          => 'date',
				'id'            => 'start',
			);
			$settings->display_text_field( $args );

			// Check if large date in the future and change it to no date
			$end = isset( $data['end'] ) ? self::MAX_DATE === $data['end'] ? '' : $data['end'] : '';
			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'End Date', 'weop' ),
				'classes'       => 'width-100',
				'value'         => $end,
				'name'          => 'end',
				'type'          => 'date',
				'id'            => 'end',
			);
			$settings->display_text_field( $args );

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'Company', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['company'] ) ? $data['company'] : '',
				'name'          => 'company',
				'id'            => 'company',
			);
			$settings->display_text_field( $args );

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'Company URL', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['company_url'] ) ? $data['company_url'] : '',
				'name'          => 'company_url',
				'id'            => 'company_url',
			);
			$settings->display_text_field( $args );

			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'Location', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['location'] ) ? $data['location'] : '',
				'name'          => 'location',
				'id'            => 'location',
			);
			$settings->display_text_field( $args );

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
			$data = sanitize_text_field( $_POST['start'] );
			update_post_meta( $post_id, '_meta_jobs_start', $data );
			$data = sanitize_text_field( $_POST['end'] );
			if ('' === $data) {
				// No end dat set to large date in the future
				$data = self::MAX_DATE;
			}
			update_post_meta( $post_id, '_meta_jobs_end', $data );
			$data = sanitize_text_field( $_POST['company'] );
			update_post_meta( $post_id, '_meta_jobs_company', $data );
			$data = esc_url_raw( $_POST['company_url'], array( 'http', 'https' ) );
			update_post_meta( $post_id, '_meta_jobs_company_url', $data );
			$data = sanitize_text_field( $_POST['location'] );
			update_post_meta( $post_id, '_meta_jobs_location', $data );
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
			$newcols['title'] = 'Name';
			unset( $columns['title'] );
			// Our custom meta data columns.
			$newcols['start']    = __( 'Start Date', 'weop' );
			$newcols['end']      = __( 'End Date', 'weop' );
			$newcols['company']  = __( 'Company', 'weop' );
			$newcols['location'] = __( 'Location', 'weop' );
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

			if ( 'start' === $column_name ) {
				echo esc_attr( $data['start'] );
			} elseif ( 'end' === $column_name ) {
				// MAX_DATE is a large date in the future representing being presently at the job
				echo ( self::MAX_DATE === $data['end'] ) ? 'Present' : esc_attr( $data['end'] );
			} elseif ( 'company' === $column_name ) {
				if ( isset( $data['company_url'] ) ) {
					echo '<a href="' . esc_url( $data['company_url'] ) .
					'" target="_blank" title="Go To ' .
					esc_attr( $data['company'] ) . '">' .
					esc_attr( $data['company'] ) . '</a>';
				} else {
					echo esc_attr( $data['company'] );
				}
			} elseif ( 'location' === $column_name ) {
				echo esc_attr( $data['location'] );
			}
		}

		/**
		 * Sort the custom post type by meta data
		 */
		public function table_sort( $columns ) {
			$columns['start'] = 'start';
			$columns['end'] = 'end';
			$columns['company'] = 'company';
			$columns['location'] = 'location';

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
				case 'start':
				case 'end':
					$query->set( 'meta_key', $meta_key_array[ $orderby ] );
					$query->set( 'meta_type', 'DATE' );
					$query->set( 'orderby', 'meta_value' );
					break;
				case 'company':
				case 'location':
					$query->set( 'meta_key', $meta_key_array[ $orderby ] );
					$query->set( 'orderby', 'meta_value' );
				break;
				default:
					break;
			}
		}

	}

}
