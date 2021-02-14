<?php
/**
 * Activities custom post type
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Activities
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

use WEOP\WEOP_Plugin;
use WEOP\Classes\Settings;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Activities' ) ) {

	/**
	 * Activities custom post type
	 */
	class Activities {

		const MAX_DATE       = '9999-12-31';
		const POST_TYPE      = 'activities';
		const META_BOX_DATA  = 'weop_activities_save_meta_box_data';
		const META_BOX_NONCE = 'weo_activities_meta_box_nonce';

		/**
		 * The maximum year.
		 */
		private $max_year = 0;

		/**
		 * Return the meta key
		 */
		public static function get_meta_key() {
			return array(
				'start' => '_meta_activities_start',
				'end'   => '_meta_activities_end',
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

			$start = get_post_meta( $post_id, $meta_key_array['start'], true );
			$end   = get_post_meta( $post_id, $meta_key_array['end'], true );

			$data = array(
				'start' => sanitize_text_field( $start ),
				'end'   => sanitize_text_field( $end ),
			);

			return $data;
		}

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->max_year = gmdate( 'Y' );
			add_action( 'init', array( $this, 'register' ) );
			add_action( 'save_post', array( $this, 'save_meta' ), 1, 2 );
			add_filter( 'manage_edit-' . self::POST_TYPE . '_columns', array( $this, 'table_head' ) );
			add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'table_content' ), 10, 2 );
			add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'table_sort' ) );
			add_action( 'pre_get_posts', array( $this, 'custom_orderby' ) );
			add_shortcode( 'weop_activities', array( $this, 'get_activities' ) );
		}

		/**
		 * Register the post type
		 */
		public function register() {
			$labels = array(
				'name'               => __( 'Activities', 'weop' ),
				'singular_name'      => __( 'Activity', 'weop' ),
				'menu_name'          => __( 'Activity', 'weop' ),
				'parent_item_colon'  => __( 'Parent Activity', 'weop' ),
				'all_items'          => __( 'All Activities', 'weop' ),
				'view_item'          => __( 'View Activity', 'weop' ),
				'add_new_item'       => __( 'Add New Activity', 'weop' ),
				'add_new'            => __( 'Add New', 'weop' ),
				'edit_item'          => __( 'Edit Activity', 'weop' ),
				'update_item'        => __( 'Update Activity', 'weop' ),
				'search_items'       => __( 'Search Activities', 'weop' ),
				'not_found'          => __( 'Activity Not Found', 'weop' ),
				'not_found_in_trash' => __( 'Activity Not Found in Trash', 'weop' ),
			);

			$args = array(
				'label'                => __( 'activity', 'weop' ),
				'description'          => __( 'Activity', 'weop' ),
				'labels'               => $labels,
				'supports'             => array( 'title' ),
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
				__( 'Activity Information', 'weop' ),
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
				'label-text'    => __( 'Start Year', 'weop' ),
				'classes'       => 'width-100',
				'value'         => isset( $data['start'] ) ? $data['start'] : '',
				'type'          => 'date',
				'name'          => 'start',
				'id'            => 'start',
			);
			$settings->display_text_field( $args );

			$end  = isset( $data['end'] ) ? self::MAX_DATE === $data['end'] ? '' : $data['end'] : '';
			$args = array(
				'label-classes' => 'input-label',
				'label-text'    => __( 'End Year', 'weop' ),
				'classes'       => 'width-100',
				'value'         => $data,
				'type'          => 'date',
				'name'          => 'end',
				'id'            => 'end',
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
			$meta_key_array = self::get_meta_key();

			$data = sanitize_text_field( $_POST['start'] );
			update_post_meta( $post_id, $meta_key_array['start'], $data );
			$data = sanitize_text_field( $_POST['end'] );
			if ( '' === $data ) {
				// No end dat set to large date in the future.
				$data = self::MAX_DATE;
			}
			update_post_meta( $post_id, $meta_key_array['end'], $data );
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
			$newcols['title'] = __( 'Activity', 'weop' );
			unset( $columns['title'] );
			// Our custom meta data columns.
			$newcols['start'] = __( 'Start Date', 'weop' );
			$newcols['end']   = __( 'End Date', 'weop' );
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
				$date = \DateTime::createFromFormat( 'Y-m-d', $data['start'] );
				echo esc_attr( date_format( $date, 'F Y' ) );
			} elseif ( 'end' === $column_name ) {
				// MAX_DATE is a large date in the future representing being presently at the job.
				if ( self::MAX_DATE === $data['end'] ) {
					echo 'Present';
				} else {
					$date = \DateTime::createFromFormat( 'Y-m-d', $data['end'] );
					echo esc_attr( date_format( $date, 'F Y' ) );
				}
			}
		}

		/**
		 * Sort the custom post type by meta data
		 *
		 * @param array $columns Array of columns.
		 */
		public function table_sort( $columns ) {
			$columns['start'] = 'start';
			$columns['end']   = 'end';

			return $columns;
		}

		/**
		 * Custom order by function
		 *
		 * @param object $query The WordPress database query object.
		 */
		public function custom_orderby( $query ) {
			if ( false === is_admin() ) {
				return;
			}

			if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
				return;
			}

			$meta_key_array = self::get_meta_key();
			$orderby        = $query->get( 'orderby' );

			switch ( $orderby ) {
				case 'start':
				case 'end':
					$query->set( 'meta_key', $meta_key_array[ $orderby ] );
					$query->set( 'meta_type', 'DATE' );
					$query->set( 'orderby', 'meta_value' );
					break;
				default:
					break;
			}
		}

		/**
		 * Shortcode to return all the activities passing in an optional date format
		 *
		 * [weop_activities date_format="F Y"]
		 *
		 * @param array $atts Array of shortcode attributes.
		 *
		 * @return string The HTML string of all the activities
		 */
		public function get_activities( $atts ) : string {

			$meta_key_array = self::get_meta_key();

			$args = array(
				'post_type'      => 'activities',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_type'      => 'DATE',
				'meta_query'     => array(
					'start'  => array(
						'key'     => $meta_key_array['start'],
						'compare' => 'EXISTS',
					),
					'end'    => array(
						'key'     => $meta_key_array['end'],
						'compare' => 'EXISTS',
					),
				),
				'orderby'        => array(
					'end'   => 'DESC',
					'start' => 'DESC',
				),
			);

			$loop = new \WP_Query( apply_filters( 'weop_activities_query', $args ) );

			$atts = shortcode_atts(
				array(
					'date_format' => 'F Y',
				),
				$atts,
				'get_activities'
			);

			$html = '';
			if ( $loop->have_posts() ) {
				while ( $loop->have_posts() ) {
					$loop->the_post();
					$post  = $loop->post;
					$data  = self::get_data( $post->ID );

					do_action( 'weop_activities_before' );
					$html .= '<div class="activity" id="activity-' . $post->ID . '">';
					$html .= '<span class="activity-title">' . \get_the_content( $post->ID ) . '</span>';

					$date  = \DateTime::createFromFormat( 'Y-m-d', $data['start'] );
					$start = date_format( $date, $atts['date_format'] );

					// MAX_DATE is a large date in the future representing being presently at the job.
					if ( self::MAX_DATE === $data['end'] ) {
						$end = 'Present';
					} else {
						$date = \DateTime::createFromFormat( 'Y-m-d', $data['end'] );
						$end  = date_format( $date, $atts['date_format'] );
					}

					$html .= '<span class="activity-date">' . $start . ' to ' . $end . '</span>';
					$html .= '<div class="activity-divider"></div>';
					$html .= '</div>';
					do_action( 'weop_activities_after' );
				}
			}
			\wp_reset_postdata();

			return apply_filters( 'weop_activities_html', $html );
		}

	}

}
