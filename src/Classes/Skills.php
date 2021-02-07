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

		/**
		 * Return the post type
		 *
		 * @return string The post type
		 */
		public static function get_post_type() : string {
			return self::POST_TYPE;
		}

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

			);

			register_post_type( self::POST_TYPE, $args );

		}

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
