<?php
/**
 * Administration Settings
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Settings
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

namespace WEOP\Classes;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Display the settings input/checkbox
 */
class Settings {
	/**
	 * Display a checkbox field
	 *
	 * @param array $args The arguments.
	 */
	public function display_checkbox_field( $args ) {

		$label_classes = isset( $args['label-classes'] ) ? ' class="' . $args['label-classes'] . '" ' : '';
		$label_text    = isset( $args['label-text'] ) ? $args['label-text'] : '';
		$classes       = isset( $args['classes'] ) ? ' class="' . $args['classes'] . '" ' : '';
		$name          = isset( $args['name'] ) ? ' name="' . $args['name'] . '" ' : '';
		$id            = isset( $args['id'] ) ? ' id="' . $args['id'] . '" ' : '';

		?>
		<input type="checkbox" value="1" <?php checked( '1', $args['value'] ); ?> <?php echo wp_kses( $classes . $name . $id, wp_kses_allowed_html( 'data' ) ); ?>>
		<span <?php echo wp_kses( $label_classes, wp_kses_allowed_html( 'data' ) ); ?>><?php echo wp_kses( $label_text, wp_kses_allowed_html( 'data' ) ); ?></span>
		<?php

	}

	/**
	 * Display a select field
	 *
	 * @param array $args The arguments.
	 */
	public function display_select_field( $args ) {

		$label_classes = isset( $args['label-classes'] ) ? ' class="' . $args['label-classes'] . '" ' : '';
		$label_text    = isset( $args['label-text'] ) ? $args['label-text'] : '';
		$classes       = isset( $args['classes'] ) ? ' class="' . $args['classes'] . '" ' : '';
		$options       = isset( $args['options'] ) ? $args['options'] : array();
		$value         = isset( $args['value'] ) ? $args['value'] : '';
		$name          = isset( $args['name'] ) ? ' name="' . $args['name'] . '" ' : '';
		$id            = isset( $args['id'] ) ? ' id="' . $args['id'] . '" ' : '';

		?>
		<select <?php echo wp_kses( $classes . $name . $id, wp_kses_allowed_html( 'data' ) ); ?>>
		<?php
		foreach ( $options as $opt_value => $text ) {
			$selected = $opt_value == $value ? 'selected' : '';
		?>
			<option value="<?php echo $opt_value; ?>" <?php echo $selected; ?>><?php echo $text; ?></option>
		<?php } ?>
		</select>
		<br>
		<span <?php echo wp_kses( $label_classes, wp_kses_allowed_html( 'data' ) ); ?>><?php echo wp_kses( $label_text, wp_kses_allowed_html( 'data' ) ); ?></span>
		<?php

	}

	/**
	 * Display a text input field
	 *
	 * @param array $args The arguments.
	 */
	public function display_text_field( $args ) {

		$label_classes = isset( $args['label-classes'] ) ? ' class="' . $args['label-classes'] . '" ' : '';
		$label_text    = isset( $args['label-text'] ) ? $args['label-text'] : '';
		$required      = isset( $args['required'] ) ? ' required ' : '';
		$classes       = isset( $args['classes'] ) ? ' class="' . $args['classes'] . '" ' : '';
		$value         = isset( $args['value'] ) ? ' value="' . $args['value'] . '"' : '';
		$name          = isset( $args['name'] ) ? ' name="' . $args['name'] . '" ' : '';
		$type          = isset( $args['type'] ) ? $args['type'] : 'text';
		$step          = isset( $args['step'] ) ? ' step="' . $args['step'] . '" ' : '';
		$max           = isset( $args['max'] ) ? ' max="' . $args['max'] . '" ' : '';
		$min           = isset( $args['min'] ) ? ' min="' . $args['min'] . '" ' : '';
		$id            = isset( $args['id'] ) ? ' id="' . $args['id'] . '" ' : '';

		?>
		<input type="<?php echo $type; ?>" <?php echo wp_kses( $classes . $name . $max . $min . $step . $id . $classes . $value . $required, wp_kses_allowed_html( 'data' ) ); ?>>
		<div <?php echo wp_kses( $label_classes, wp_kses_allowed_html( 'data' ) ); ?>><?php echo wp_kses( $label_text, wp_kses_allowed_html( 'data' ) ); ?></div>
		<?php

	}
}
