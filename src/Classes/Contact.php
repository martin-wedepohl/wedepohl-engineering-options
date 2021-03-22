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
			
			if ( ! isset( $post_input['submitted'] ) ) {
				$response = '';
			} else {
				$response = $this->send_email( $post_input );
			}
			$name  = isset( $post_input['sender_name'] ) ? $post_input['sender_name'] : '';
			$email = isset( $post_input['sender_email'] ) ? $post_input['sender_email'] : '';
			$message = isset( $post_input['sender_message'] ) ? $post_input['sender_message'] : '';
			?>
<div id="contact-us-form">
	<?php echo $response; ?>
	<form id="contact-us-form" action="<?php echo esc_url( get_permalink() ); ?>" method="post">
		<label for="sender_name" class="required">Name:</label>
		<input type="text" id="name" name="sender_name" required value="<?php echo esc_attr($name); ?>">
		<label for="sender_email" class="required">Email:</label>
		<input type="text" id="email" name="sender_email" required value="<?php echo esc_attr($email); ?>">
		<label for="sender_message" class="required">Message:</label>
		<textarea type="text" id="message" name="sender_message" required><?php echo esc_textarea($message); ?></textarea>
		<label for="is_human" class="required">Human Verification:</label>
		<input type="text" id="human_test" class="human_test" name="is_human" required> + 3 = 5
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
			$post_args = [];
			$textareas = ['sender_message'];
			$ints      = ['is_human', 'submitted'];
			$emails    = ['sender_email'];

			foreach($_POST as $k => $v) {
				$v = trim( $v );	//so we are sure it is whitespace free at both ends.

				if ( in_array( $k, $ints ) ) {
					$v = filter_var( $v, FILTER_VALIDATE_INT );
				} elseif ( in_array( $k, $emails ) ) {
					$v = filter_var( $v, FILTER_VALIDATE_EMAIL );
				} else {
					//preserve newline for textarea answers.
					if ( in_array( $k, $textareas ) ) {
						$v = str_replace( "\n", "[NEWLINE]", $v );
					}
					//sanitise string.
					$v = filter_var( $v, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
					//now replace the placeholder with the original newline.
					if ( in_array( $k, $textareas ) ) {
						$v = str_replace( "[NEWLINE]", "\n", $v );
					}
				}
				$post_args[$k] = $v;
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

			if ( false === $post_input['is_human'] ) {
				return $this->generate_response( self::MISSING_CONTACT );
			}

			if ( 2 !== $post_input['is_human'] ) {
				return $this->generate_response( self::NOT_HUMAN );
			}

			if ( false === $post_input['sender_email'] ) {
				return $this->generate_response( self::EMAIL_INVALID );
			}

			if ( empty( $post_input['sender_name'] ) || empty( $post_input['sender_message'] ) ) {
				return $this->generate_response( self::MISSING_CONTACT );
			}

			// Input valid send email
			$to      = get_option( 'admin_email' );
			$subject = 'Someone sent a message from ' . get_bloginfo( 'name' );
			$headers = "From: {$post_input['sender_email']}\r\nReply-To: {$post_input['sender_email']}\r\n";

			if ( ! wp_mail( $to, $subject, strip_tags( $post_input['sender_message'] ), $headers ) ) {
				return $this->generate_response( self::MESSAGE_UNSENT );
			}

			return $this->generate_response( self::MESSAGE_SENT, false );
		}

	}

}
