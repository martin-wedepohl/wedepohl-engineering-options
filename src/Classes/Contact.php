<?php
/**
 * Contact Us Class
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Contact
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Contact' ) ) {

	/**
	 * All the Contact Us handling
	 */
	class Contact {

		const NOT_HUMAN       = 'Human verification failed.';
		const MISSING_CONTACT = 'Please supply all information.';
		const EMAIL_INVALID   = 'Email Address Invalid.';
		const NONCE_INVALID   = 'Form Submission Invalid.';
		const MESSAGE_UNSENT  = 'Message was not sent. Try Again.';
		const MESSAGE_SENT    = 'Thanks! Your message has been sent.';

		/**
		 * Get Options callback
		 *
		 * @var $main_plugin Callback function to get the plugin options
		 * @access private
		 */
		private $main_plugin = null;

		/**
		 * Class constructor.
		 *
		 * @param function $plugin Callback to get the plugin options.
		 */
		public function __construct( $plugin ) {
			$this->main_plugin = $plugin;

			// add_action( 'enqueue_block_editor_assets', array( $this, 'disable_editor_fullscreen' ) );

		}

		public function display_form() {
			$post_input = $this->get_post_args();
			
			if ( isset( $post_input['submitted'] ) ) {
				$response = '';
			} else {
				$response = $this->send_email( $post_input );
			}
			$name  = isset( $post_input['name'] ) ? $post_input['name'] : '';
			$email = isset( $post_input['email'] ) ? $post_input['email'] : '';
			$message = isset( $post_input['email'] ) ? $post_input['email'] : '';
			?>
<div id="contact-us-form">
	<?php echo $response; ?>
	<form action="<?php the_permalink(); ?>" method="post">
		<label for="name" class="required">Name:</label>
		<input type="text" id="name" name="name" required value="<?php echo esc_attr($name); ?>">
		<label for="email" class="required">Email:</label>
		<input type="text" id="email" name="email" required value="<?php echo esc_attr($email); ?>">
		<label for="message" class="required">Message:</label>
		<textarea type="text" id="message" name="message" required><?php echo esc_textarea($message); ?></textarea>
		<label for="human_test" class="required">Human Verification:</label>
		<input type="text" id="human_test" class="human_test" name="human_test" required> + 3 = 5
		<input type="hidden" name="submitted" value="1">
		<?php wp_nonce_field( 'weop-contact-form', 'weop-contact-form-nonce' ); ?>
		<input type="submit" id="submit" class="disabled" value="Send">
	</form>
</div>
			<?php
		}

		/**
		 * Generate contact form response
		 *
		 * @param string $message  The message.
		 * @param bool   $is_error If th message is an error (default = true)
		 *
		 * @return string $response
		 */
		public function generate_response( $message, $is_error = true ): string {

			if ( $is_error ) {
				return "<div class='error'>{$message}</div>";
			}

			return "<div class='success'>{$message}</div>";

		}

		public function get_post_args(): array {
			$args = [
				'human_test'              => FILTER_VALIDATE_INT,
				'name'                    => FILTER_SANITIZE_STRING,
				'message'                 => FILTER_SANITIZE_STRING,
				'weop-contact-form-nonce' => FILTER_SANITIZE_STRING,
				'_wp_http_referer'        => FILTER_SANITIZE_STRING,
				'email'                   => FILTER_VALIDATE_EMAIL,
			];

			$post_args = filter_input_array( INPUT_POST, $args );

			if ( null === $post_args ) {
				return [];
			}

			return $post_args;

		}

		public function send_email( $post_input ): string {

			// Validate nonce
			if ( false === wp_verify_nonce( $post_input['weop-contact-form-nonce'], 'weop-contact-form' ) ) {
				return $this->generate_response( self::NONCE_INVALID);
			}
			
			if ( '/contact-us/' !== $post_input['_wp_http_referer'] ) {
				return $this->generate_response( self::NONCE_INVALID);
			}

			if ( false === $post_input['human_test'] ) {
				return $this->generate_response( self::MISSING_CONTACT );
			}

			if ( 2 !== $post_input['human_test'] ) {
				return $this->generate_response( self::NOT_HUMAN );
			}

			if ( false === $post_input['email'] ) {
				return $this->generate_response( self::EMAIL_INVALID );
			}

			if ( empty( $post_input['name'] ) || empty( $post_input['message'] ) ) {
				return $this->generate_response( self::MISSING_CONTACT );
			}

			// Input valid send email
			$to      = get_option( 'admin_email' );
			$subject = 'Someone sent a message from ' . get_bloginfo( 'name' );
			$headers = "From: {$post_input['email']}\r\nReply-To: {$post_input['email']}\r\n";

			if ( ! wp_mail( $to, $subject, strip_tags( $post_input['message'] ), $headers ) ) {
				return $this->generate_response( self::MESSAGE_UNSENT );
			}

			return $this->generate_response( self::MESSAGE_SENT, false );
		}

	}

}
