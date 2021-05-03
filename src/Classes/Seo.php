<?php
/**
 * Comments functions
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Seo
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Seo' ) ) {

	/**
	 * Enable SEO.
	 */
	class Seo {

		/**
		 * Database options
		 *
		 * @var array $options Plugin options in the database
		 * @access private
		 */
		private $options;

		/**
		 * The title separators array
		 *
		 * @var array $separators Array of potential title separators
		 * @access private
		 */
		private $separators;

		/**
		 * Class constructor.
		 *
		 * @global $wp_version The WordPress version
		 *
		 * @param function $plugin Callback to get the plugin options.
		 */
		public function __construct( $plugin ) {

			$this->options    = $plugin->get_options();
			$this->separators = $plugin->get_separators();

			// Set up for SEO.
			add_action( 'after_setup_theme', array( $this, 'allow_title_modification' ), 11 );
			add_filter( 'document_title_separator', array( $this, 'change_separator' ), 10 );
			add_filter( 'document_title_parts', array( $this, 'change_title' ) );
			add_action( 'wp_head', array( $this, 'meta_description' ) );
			add_action( 'load-post.php', array( $this, 'meta_box_setup' ) );
			add_action( 'load-post-new.php', array( $this, 'meta_box_setup' ) );

		}

		/**
		 * Allow modification of the title
		 */
		public function allow_title_modification() {

			add_theme_support( 'title-tag' );

		}

		/**
		 * Change the separator
		 *
		 * @param string $sep Current separator.
		 *
		 * @return string The new separator.
		 */
		public function change_separator( $sep ) {

			if ( '' === $this->options['title_separator'] ) {
				return $sep;
			}

			return $this->separators[ $this->options['title_separator'] ];

		}

		/**
		 * Change the title by setting the various parts.
		 * Need to override title and tagline.
		 *
		 * @param array $title_parts The array of parts of the title.
		 *
		 * @return array The modified parts of the title.
		 */
		public function change_title( $title_parts ) {

			global $post;

			$title = esc_attr( get_post_meta( $post->ID, 'weop_seo_title', true ) );
			$site  = isset( $title_parts['site'] ) ? $title_parts['site'] : $title_parts['tagline'];

			$new_parts = [];

			if ( empty( $title ) ) {
				if ( is_home() || is_front_page() ) {
					$title = get_bloginfo( 'name' );
				} else {
					$title = $post->post_title;
				}
			}

			$new_parts['title'] = $title;
			$new_parts['site']  = $site;

			return $new_parts;

		}

		/**
		 * Create the meta description.
		 * Limit 120 characters for mobile.
		 * Limit 158 characters for desktop.
		 *
		 * @global array $post The current post to get the content
		 */
		public function meta_description() {

			global $post;

			$description = esc_attr( get_post_meta( $post->ID, 'weop_seo_description', true ) );

			if ( empty( $description ) ) {
				if ( is_home() || is_front_page() ) {
					$description = get_bloginfo( 'description' );
				} else {
					$description  = strip_tags( $post->post_content );
					$description .= strip_shortcodes( $description );			
				}
			}
			$description = preg_replace( '!\s+!', ' ', $description );
			$description = mb_substr( $description, 0, 158, 'utf8' );

			echo "<meta name=\"description\" content=\"${description}\" />"; 

		}

		/**
		 * Set up the meta box.
		 */
		public function meta_box_setup() {

			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			add_action( 'save_post', array( $this, 'save_meta_box' ) );

		}

		/**
		 * Add a meta box to all pages/posts.
		 * $GLOBALS['wp_post_types'] contains
		 *    [0] => post
		 *    [1] => page
		 *    [2] => attachment
		 *    [3] => revision
		 *    [4] => nav_menu_item
		 *    [5] => ... custom post types
		 */
		public function add_meta_box() {

			foreach ( array_keys( $GLOBALS['wp_post_types'] ) as $post_type ) {
				if ( in_array( $post_type, array( 'post', 'page' ) ) ) {
					$id       = 'weop-seo-meta-box';
					$title    = esc_html__( 'SEO', 'weop' );
					$callback = array( $this, 'meta_box' );
					add_meta_box( $id, $title, $callback, $post_type, 'normal', 'default' );
				}
			}

		}

		/**
		 * Save the meta box information.
		 *
		 * @param int   $post_id The Post ID.
		 */
		public function save_meta_box( $post_id ) {

			global $post;

			$post_type = get_post_type_object( $post->post_type );

			/*
			 * Verify everything is correct before proceeding.
			 * Verify nonce.
			 * Verify that the user can edit the post
			 */
			if (
				! isset( $_POST['weop_seo_nonce'] ) ||
				! wp_verify_nonce( $_POST['weop_seo_nonce'], basename( __FILE__ ) ) ||
				! current_user_can( $post_type->cap->edit_post, $post_id )
			) {
				return $post_id;
			}

			// Process the SEO title.
			$new_meta_value = ( isset( $_POST['weop-seo-title'] ) ? sanitize_text_field( $_POST['weop-seo-title'] ) : '' );		// Get and sanitize title
			$meta_key       = 'weop_seo_title';
			$meta_value     = get_post_meta( $post_id, $meta_key, true );     // Get previous value.

			if ( $new_meta_value && '' === $meta_value ) {                    // Have new value and no old one - add it.
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );
			} elseif ( $new_meta_value && $new_meta_value !== $meta_value ) { // If the new value does not match the old value, update it.
				update_post_meta( $post_id, $meta_key, $new_meta_value );
			} elseif ( '' === $new_meta_value && $meta_value ) {              // If there is no new meta value but an old value exists, delete it.
				delete_post_meta( $post_id, $meta_key, $meta_value );
			}

			// Process the SEO description.
			$new_meta_value = ( isset( $_POST['weop-seo-description'] ) ? sanitize_textarea_field( $_POST['weop-seo-description'] ) : '' );		// Get and sanitize description
			$meta_key       = 'weop_seo_description';
			$meta_value     = get_post_meta( $post_id, $meta_key, true );	  // Get previous value.

			if ( $new_meta_value && '' === $meta_value ) {                    // Have new value and no old one - add it.
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );
			} elseif ( $new_meta_value && $new_meta_value !== $meta_value ) { // If the new value does not match the old value, update it.
				update_post_meta( $post_id, $meta_key, $new_meta_value );
			} elseif ( '' === $new_meta_value && $meta_value ) {              // If there is no new meta value but an old value exists, delete it.
				delete_post_meta( $post_id, $meta_key, $meta_value );
			}

		}

		/**
		 * SEO Meta Box callback.
		 *
		 * @param array $post The post where the callback is found.
		 */
		public function meta_box( $post ) {

			wp_nonce_field( basename( __FILE__ ), 'weop_seo_nonce' );
			?>
<p>
	<label for="weop-seo-title"><?php _e( 'SEO title for the page', 'weop' ); ?></label>
	<input class="widefat" type="text" name="weop-seo-title" id="weop-seo-title" value="<?php echo esc_attr( get_post_meta( $post->ID, 'weop_seo_title', true ) ); ?>" size="30" />
	<label for="weop-seo-description"><?php _e( 'SEO description for the page', 'weop' ); ?></label>
	<textarea class="widefat" name="weop-seo-description" id="weop-seo-description"><?php echo esc_attr( get_post_meta( $post->ID, 'weop_seo_description', true ) ); ?></textarea>
</p>
			<?php
		}

	}

}