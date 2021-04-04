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

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

defined( 'ABSPATH' ) || die( '' );

if ( ! class_exists( 'Contact' ) ) {

	/**
	 * All the Contact Us handling
	 */
	class Contact {

		const NOT_HUMAN       = '<div class="error">Human verification failed.</div>';
		const MISSING_CONTACT = '<div class="error">Please supply all information.</div>';
		const EMAIL_INVALID   = '<div class="error">Email Address Invalid.</div>';
		const NONCE_INVALID   = '<div class="error">Form Submission Invalid.</div>';
		const MESSAGE_UNSENT  = '<div class="error">Message was not sent. Try Again.</div>';
		const MESSAGE_SENT    = '<div class="success">Thanks! Your message has been sent.</div>';

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

			\add_action(  'wp_enqueue_scripts', array( $this, 'load_dashicons_front_end' ) );
		}

		/**
		 * Enqueue dashicons so they can be used on the front end.
		 */
		public function load_dashicons_front_end() {
			\wp_enqueue_style( 'dashicons' );
		}

		/**
		 * Display the contact us form.
		 */
		public function display_form() {

			// Get the post arguments.
			$post_input = $this->get_post_args();

			$success = false;
			// New contact form.
			if ( ! isset( $post_input['submitted'] ) ) {
				$response = '';
			} else {
				// Send the email.
				$response = $this->send_email( $post_input );
				if ( self::MESSAGE_SENT === $response ) {
					$success = true;
				}
			}
			// Blank fields if it is a success.
			$name    = $success ? '' : isset( $post_input['sender_name'] ) ? $post_input['sender_name'] : '';
			$email   = $success ? '' : isset( $post_input['sender_email'] ) ? $post_input['sender_email'] : '';
			$message = $success ? '' : isset( $post_input['sender_message'] ) ? $post_input['sender_message'] : '';

			$captcha_file = plugin_dir_url( __DIR__ ) . 'utilities/captcha.php';
			?>
<div class="contact-us-div">
	<?php echo $response; ?>
	<form class="contact-us-form" action="<?php echo esc_url( get_permalink() ); ?>" method="post">
		<label for="sender_name" class="required">Name:</label>
		<input type="text" id="name" name="sender_name" required value="<?php echo esc_attr($name); ?>">
		<label for="sender_email" class="required">Email:</label>
		<input type="text" id="email" name="sender_email" required value="<?php echo esc_attr($email); ?>">
		<label for="sender_message" class="required">Message:</label>
		<textarea type="text" id="message" name="sender_message" required><?php echo esc_textarea($message); ?></textarea>
		<div class="captcha-div">
			<img src="<?php echo plugin_dir_url( __DIR__ ); ?>utilities/captcha.php" alt="CAPTCHA" class="captcha-image">
			<span title="Refresh Captcha" id="captcha-refresh" data-file="<?php echo esc_url( $captcha_file ); ?>" class="content-icon dashicons dashicons-image-rotate"></span>
		</div>
		<label for="is_human" class="required">Enter characters show in image above:</label>
		<input type="text" id="human_test" class="human_test" name="is_human" required>
		<input type="hidden" name="submitted" value="1">
		<?php wp_nonce_field( 'weop-contact-form', 'weop-contact-form-nonce' ); ?>
		<input type="submit" id="submit" class="disabled" value="Send">
	</form>
</div>
			<?php
		}

		/**
		 * Get the post arguments and process them according to type.
		 *
		 * @return array Named array of post arguments.
		 */
		public function get_post_args(): array {
			$post_args = array();
			$textareas = array( 'sender_message' );
			$ints      = array( 'submitted' );
			$emails    = array( 'sender_email' );

			foreach ( $_POST as $k => $v ) {
				// So we are sure it is whitespace free at both ends.
				$v = trim( $v );

				if ( in_array( $k, $ints, true ) ) {
					$v = filter_var( $v, FILTER_VALIDATE_INT );
				} elseif ( in_array( $k, $emails, true ) ) {
					$v = filter_var( $v, FILTER_VALIDATE_EMAIL );
				} else {
					// Preserve newline for textarea answers.
					if ( in_array( $k, $textareas, true ) ) {
						$v = str_replace( "\n", '[NEWLINE]', $v );
					}
					// Sanitise string.
					$v = filter_var( $v, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
					// Now replace the placeholder with the original newline.
					if ( in_array( $k, $textareas, true ) ) {
						$v = str_replace( '[NEWLINE]', "\n", $v );
					}
				}
				$post_args[ $k ] = $v;
			}

			return $post_args;

		}

		/**
		 * Send the email after validating the input.
		 *
		 * @param array $post_input Name array of post input values.
		 *
		 * @return string The response
		 */
		public function send_email( array $post_input ): string {

			// Validate nonce.
			if ( false === wp_verify_nonce( $post_input['weop-contact-form-nonce'], 'weop-contact-form' ) ) {
				return self::NONCE_INVALID;
			}

			// Validate coming from the contact-us page.
			if ( '/contact-us/' !== $post_input['_wp_http_referer'] ) {
				return self::NONCE_INVALID;
			}

			// Validate the captcha string.
			if ( $_SESSION['captcha_text'] !== strtoupper( $post_input['is_human'] ) ) {
				return self::NOT_HUMAN;
			}

			// Ensure that the email address is valid.
			if ( false === $post_input['sender_email'] ) {
				return self::EMAIL_INVALID;
			}

			// Need an name and a message.
			if ( empty( $post_input['sender_name'] ) || empty( $post_input['sender_message'] ) ) {
				return self::MISSING_CONTACT;
			}

			// Input valid send email.
			$to      = get_option( 'admin_email' );
			$subject = 'Someone sent a message from ' . get_bloginfo( 'name' );
			$headers = "From: {$post_input['sender_name']} <{$post_input['sender_email']}>\r\nReply-To: {$post_input['sender_email']}\r\n";

			if ( ! wp_mail( $to, $subject, strip_tags( $post_input['sender_message'] ), $headers ) ) {
				// Error sending email.
				return self::MESSAGE_UNSENT;
			}

			// Success.
			return self::MESSAGE_SENT;
		}

	}

}
