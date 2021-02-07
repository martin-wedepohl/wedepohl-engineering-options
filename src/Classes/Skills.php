<?php
/**
 * Skills custom post type
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Skills
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

use WEOP\WEOP_Plugin;
use WEOP\Classes\Settings;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Skills' ) ) {

	/**
	 * Skills custom post type
	 */
	class Skills {

		const POST_TYPE      = 'skills';
		const META_BOX_DATA  = 'weop_skills_save_meta_box_data';
		const META_BOX_NONCE = 'weo_skills_meta_box_nonce';

		// /**
		//  * Return the meta key
		//  *
		//  * @return array The array of meta keys
		//  */
		// public static function get_meta_key() : array {
		// 	return array(
		// 		'start'       => '_meta_skills_start',
		// 		'end'         => '_meta_skills_end',
		// 		'company'     => '_meta_jobs_company',
		// 		'company_url' => '_meta_jobs_company_url',
		// 		'location'    => '_meta_jobs_location',
		// 	);
		// }

		/**
		 * Return the post type
		 *
		 * @return string The post type
		 */
		public static function get_post_type() : string {
			return self::POST_TYPE;
		}

		// /**
		//  * Get all the job meta data for the post id
		//  *
		//  * All the data will be sanitized.
		//  *
		//  * @param int $post_id The ID of the post.
		//  *
		//  * @return array Associative array with all the meta data
		//  */
		// public static function get_data( $post_id ) : array {

		// 	$meta_key_array = self::get_meta_key();

		// 	$start       = get_post_meta( $post_id, $meta_key_array['start'], true );
		// 	$end         = get_post_meta( $post_id, $meta_key_array['end'], true );
		// 	$company     = get_post_meta( $post_id, $meta_key_array['company'], true );
		// 	$company_url = get_post_meta( $post_id, $meta_key_array['company_url'], true );
		// 	$location    = get_post_meta( $post_id, $meta_key_array['location'], true );

		// 	$data = array(
		// 		'title'       => get_the_title( $post_id ),
		// 		'start'       => sanitize_text_field( $start ),
		// 		'end'         => sanitize_text_field( $end ),
		// 		'company'     => sanitize_text_field( $company ),
		// 		'company_url' => esc_url( $company_url ),
		// 		'location'    => sanitize_text_field( $location ),
		// 	);

		// 	return $data;
		// }

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register' ) );
			// add_action( 'save_post', array( $this, 'save_meta' ), 1, 2 );
			// add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'table_head' ) );
			// add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'table_content' ), 10, 2 );
			// add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'table_sort' ) );
			// add_action( 'pre_get_posts', array( $this, 'custom_orderby' ) );
			add_shortcode( 'weop_skills', array( $this, 'get_skills' ) );
		}

		/**
		 * Register the post type
		 */
		public function register() {
			$labels = array(
				'name'               => __( 'Skills', 'weop' ),
				'singular_name'      => __( 'Skill', 'weop' ),
				'menu_name'          => __( 'Skills', 'weop' ),
				'parent_item_colon'  => __( 'Parent Skill', 'weop' ),
				'all_items'          => __( 'All Skills', 'weop' ),
				'view_item'          => __( 'View Skill', 'weop' ),
				'add_new_item'       => __( 'Add New Skill', 'weop' ),
				'add_new'            => __( 'Add New', 'weop' ),
				'edit_item'          => __( 'Edit Skill', 'weop' ),
				'update_item'        => __( 'Update Skill', 'weop' ),
				'search_items'       => __( 'Search Skills', 'weop' ),
				'not_found'          => __( 'Skill Not Found', 'weop' ),
				'not_found_in_trash' => __( 'Skill Not Found in Trash', 'weop' ),
			);

			$args = array(
				'label'                => __( 'skill', 'weop' ),
				'description'          => __( 'Skills', 'weop' ),
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
				// 'register_meta_box_cb' => array( $this, 'register_meta_box' ),

			);

			register_post_type( self::POST_TYPE, $args );

		}

		// /**
		//  * Add the meta box to the custom post type
		//  */
		// public function register_meta_box() {
		// 	add_meta_box(
		// 		'information_section',
		// 		__( 'Job Information', 'weop' ),
		// 		array( $this, 'meta_box' ),
		// 		self::POST_TYPE,
		// 		'side',
		// 		'high'
		// 	);
		// }

		// /**
		//  * Display the meta box
		//  *
		//  * @global type $post - The current post
		//  */
		// public function meta_box() {
		// 	global $post;
		// 	// Nonce field to validate form request from current site.
		// 	wp_nonce_field( self::META_BOX_DATA, self::META_BOX_NONCE );

		// 	// Get all the meta data.
		// 	$data = self::get_data( $post->ID );

		// 	$settings = new Settings();

		// 	$args = array(
		// 		'label-classes' => 'input-label',
		// 		'label-text'    => __( 'Start Date', 'weop' ),
		// 		'required'      => true,
		// 		'classes'       => 'width-100',
		// 		'value'         => isset( $data['start'] ) ? $data['start'] : '',
		// 		'name'          => 'start',
		// 		'type'          => 'date',
		// 		'id'            => 'start',
		// 	);
		// 	$settings->display_text_field( $args );

		// 	// Check if large date in the future and change it to no date.
		// 	$end  = isset( $data['end'] ) ? self::MAX_DATE === $data['end'] ? '' : $data['end'] : '';
		// 	$args = array(
		// 		'label-classes' => 'input-label',
		// 		'label-text'    => __( 'End Date', 'weop' ),
		// 		'classes'       => 'width-100',
		// 		'value'         => $end,
		// 		'name'          => 'end',
		// 		'type'          => 'date',
		// 		'id'            => 'end',
		// 	);
		// 	$settings->display_text_field( $args );

		// 	$args = array(
		// 		'label-classes' => 'input-label',
		// 		'label-text'    => __( 'Company', 'weop' ),
		// 		'classes'       => 'width-100',
		// 		'value'         => isset( $data['company'] ) ? $data['company'] : '',
		// 		'name'          => 'company',
		// 		'id'            => 'company',
		// 	);
		// 	$settings->display_text_field( $args );

		// 	$args = array(
		// 		'label-classes' => 'input-label',
		// 		'label-text'    => __( 'Company URL', 'weop' ),
		// 		'classes'       => 'width-100',
		// 		'value'         => isset( $data['company_url'] ) ? $data['company_url'] : '',
		// 		'name'          => 'company_url',
		// 		'id'            => 'company_url',
		// 	);
		// 	$settings->display_text_field( $args );

		// 	$args = array(
		// 		'label-classes' => 'input-label',
		// 		'label-text'    => __( 'Location', 'weop' ),
		// 		'classes'       => 'width-100',
		// 		'value'         => isset( $data['location'] ) ? $data['location'] : '',
		// 		'name'          => 'location',
		// 		'id'            => 'location',
		// 	);
		// 	$settings->display_text_field( $args );

		// }

		// /**
		//  * Save the meta box data
		//  *
		//  * @param int   $post_id The post ID.
		//  * @param array $post    The post.
		//  */
		// public function save_meta( $post_id, $post ) {
		// 	// Checks save status.
		// 	$is_autosave    = wp_is_post_autosave( $post_id );
		// 	$is_revision    = wp_is_post_revision( $post_id );
		// 	$is_valid_nonce = ( isset( $_POST[ self::META_BOX_NONCE ] ) && wp_verify_nonce( $_POST[ self::META_BOX_NONCE ], self::META_BOX_DATA ) ) ? true : false;
		// 	$can_edit       = current_user_can( 'edit_post', $post_id );
		// 	// Exits script depending on save status.
		// 	if ( $is_autosave || $is_revision || ! $is_valid_nonce || ! $can_edit ) {
		// 		return;
		// 	}
		// 	// Now that we're authenticated, time to save the data.
		// 	$meta_key_array = self::get_meta_key();
		// 	$data           = sanitize_text_field( $_POST['start'] );
		// 	update_post_meta( $post_id, $meta_key_array['start'], $data );
		// 	$data = sanitize_text_field( $_POST['end'] );
		// 	if ( '' === $data ) {
		// 		// No end dat set to large date in the future.
		// 		$data = self::MAX_DATE;
		// 	}
		// 	update_post_meta( $post_id, $meta_key_array['end'], $data );
		// 	$data = sanitize_text_field( $_POST['company'] );
		// 	update_post_meta( $post_id, $meta_key_array['company'], $data );
		// 	$data = esc_url_raw( $_POST['company_url'], array( 'http', 'https' ) );
		// 	update_post_meta( $post_id, $meta_key_array['company_url'], $data );
		// 	$data = sanitize_text_field( $_POST['location'] );
		// 	update_post_meta( $post_id, $meta_key_array['location'], $data );
		// }

		// /**
		//  * Display the table headers for custom columns in our order.
		//  *
		//  * @param array $columns Array of headers.
		//  *
		//  * @return array Modified array of headers.
		//  */
		// public function table_head( $columns ) : array {
		// 	$newcols = array();
		// 	// Want the selection box and title (name for our custom post type) first.
		// 	$newcols['cb'] = $columns['cb'];
		// 	unset( $columns['cb'] );
		// 	$newcols['title'] = 'Job';
		// 	unset( $columns['title'] );
		// 	// Our custom meta data columns.
		// 	$newcols['start']    = __( 'Start Date', 'weop' );
		// 	$newcols['end']      = __( 'End Date', 'weop' );
		// 	$newcols['company']  = __( 'Company', 'weop' );
		// 	$newcols['location'] = __( 'Location', 'weop' );
		// 	// Want date last.
		// 	unset( $columns['date'] );
		// 	// Add all other selected columns.
		// 	foreach ( $columns as $col => $title ) {
		// 		$newcols[ $col ] = $title;
		// 	}
		// 	// Add the date back.
		// 	$newcols['date'] = 'Date';
		// 	return $newcols;
		// }

		// /**
		//  * Display the meta data associated with a post on the administration table
		//  *
		//  * @param string $column_name The header of the column.
		//  * @param int    $post_id     The ID of the post being displayed.
		//  */
		// public function table_content( $column_name, $post_id ) {
		// 	$data = self::get_data( $post_id );

		// 	if ( 'start' === $column_name ) {
		// 		$date  = \DateTime::createFromFormat( 'Y-m-d', $data['start'] );
		// 		$start = date_format( $date, 'F jS, Y' );
		// 		echo esc_html( $start );
		// 	} elseif ( 'end' === $column_name ) {
		// 		// MAX_DATE is a large date in the future representing being presently at the job.
		// 		if ( self::MAX_DATE === $data['end'] ) {
		// 			echo 'Present';
		// 		} else {
		// 			$date = \DateTime::createFromFormat( 'Y-m-d', $data['end'] );
		// 			$end  = date_format( $date, 'F jS, Y' );
		// 			echo esc_html( $end );
		// 		}
		// 	} elseif ( 'company' === $column_name ) {
		// 		if ( isset( $data['company_url'] ) ) {
		// 			echo '<a href="' . esc_url( $data['company_url'] ) .
		// 			'" target="_blank" title="Go To ' .
		// 			esc_attr( $data['company'] ) . '">' .
		// 			esc_attr( $data['company'] ) . '</a>';
		// 		} else {
		// 			echo esc_attr( $data['company'] );
		// 		}
		// 	} elseif ( 'location' === $column_name ) {
		// 		echo esc_attr( $data['location'] );
		// 	}
		// }

		// /**
		//  * Sort the custom post type by meta data
		//  *
		//  * @param array $columns The array of sortable columns.
		//  *
		//  * @return array The columns for sorting
		//  */
		// public function table_sort( $columns ) : array {
		// 	$columns['start']    = 'start';
		// 	$columns['end']      = 'end';
		// 	$columns['company']  = 'company';
		// 	$columns['location'] = 'location';

		// 	return $columns;
		// }

		// /**
		//  * Custom order by function
		//  *
		//  * @param object $query The WordPress database query object.
		//  */
		// public function custom_orderby( $query ) {
		// 	if ( false !== is_admin() )
		// 		return;

		// 	if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
		// 		return;
		// 	}

		// 	$meta_key_array = self::get_meta_key();
		// 	$orderby        = $query->get( 'orderby');

		// 	switch ( $orderby ) {
		// 		case 'start':
		// 		case 'end':
		// 			$query->set( 'meta_key', $meta_key_array[ $orderby ] );
		// 			$query->set( 'meta_type', 'DATE' );
		// 			$query->set( 'orderby', 'meta_value' );
		// 			break;
		// 		case 'company':
		// 		case 'location':
		// 			$query->set( 'meta_key', $meta_key_array[ $orderby ] );
		// 			$query->set( 'orderby', 'meta_value' );
		// 			break;
		// 		default:
		// 			break;
		// 	}
		// }

		/**
		 * Shortcode to return all the skills passing in an optional date format
		 *
		 * [weop_skills]
		 *
		 * @param array $atts Array of shortcode attributes.
		 *
		 * @return string The HTML string of all the skills
		 */
		public function get_skills( $atts ) : string {

			// $meta_key_array = self::get_meta_key();

			$args = array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'ASC',
				// 'meta_type'      => 'DATE',
				// 'meta_query'     => array(
				// 	'job_start'  => array(
				// 		'key'     => $meta_key_array['start'],
				// 		'compare' => 'EXISTS',
				// 	),
				// 	'job_end'    => array(
				// 		'key'     => $meta_key_array['end'],
				// 		'compare' => 'EXISTS',
				// 	),
				// ),
				// 'orderby'        => array(
				// 	'job_end'   => 'DESC',
				// 	'job_start' => 'ASC',
				// ),
			);

			$loop = new \WP_Query( apply_filters( 'weop_skills_query', $args ) );

			$atts = shortcode_atts(
				array(
					'date_format' => 'F Y',
				),
				$atts,
				'get_skills'
			);

			$html = '';
			if ( $loop->have_posts() ) {
				while ( $loop->have_posts() ) {
					$loop->the_post();
					$post  = $loop->post;

					do_action( 'weop_skills_before' );
					$html .= '<div class="skill" id="skill-' . $post->ID . '">';
					$html .= '<span class="skill-content">' . get_the_content( $post->ID ) . '</span>';
					$html .= '</div>';
					do_action( 'weop_skills_after' );
				}
			}
			\wp_reset_postdata();

			return apply_filters( 'weop_skills_html', $html );
		}

	}
}
