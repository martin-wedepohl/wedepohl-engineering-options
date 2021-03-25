<?php
/**
 * Captcha image generation
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  captcha
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

session_start();

const WIDTH          = 200;
const HEIGHT         = 50;
const NUM_COLORS     = 5;
const OFFSET         = 20;
const NUM_RECTANGLES = 10;

/**
 * Create Captcha String
 *
 * @param string $input    Allowable characters.
 * @param int    $strength Number of characters to display.
 *
 * @return string Captcha string.
 */
function create_captcha_string( string $input, int $strength ): string {
	$input_length   = strlen( $input );
	$captcha_string = '';
	for ( $i = 0; $i < $strength; $i++ ) {
		$captcha_string .= $input[ random_int( 0, $input_length - 1 ) ];
	}

	return $captcha_string;
}

// Create the image resource.
$image = imagecreatetruecolor( WIDTH, HEIGHT );

// Use antialias image functions.
imageantialias( $image, true );

// Generate random colors.
$colors = array();
$red    = random_int( 100, 200 );
$green  = random_int( 100, 200 );
$blue   = random_int( 100, 200 );
for ( $i = 0; $i < NUM_COLORS; $i++ ) {
	$colors[] = imagecolorallocate( $image, $red - OFFSET * $i, $green - OFFSET * $i, $blue - OFFSET * $i );
}

// Flood fill the image with the first color.
imagefill( $image, 0, 0, $colors[0] );

// Create random color rectangles.
for ( $i = 0; $i < NUM_RECTANGLES; $i++ ) {
	imagesetthickness( $image, random_int( 2, 10 ) );
	$line_color = $colors[ random_int( 1, NUM_COLORS - 1 ) ];
	imagerectangle( $image, random_int( -10, 190 ), random_int( -10, 10 ), random_int(-10, 190 ), random_int( 40, 60 ), $line_color );
}

// Create the captcha image colors.
$gray       = imagecolorallocate( $image, 200, 200, 200 );
$white      = imagecolorallocate( $image, 255, 255, 255 );
$textcolors = array( $gray, $white );

// Create the fonts array.
$fonts = array( dirname( __FILE__ ) . '\fonts\comic.ttf' );

// Create the captcha string.
$string_length   = 6;
$permitted_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$captcha_string  = create_captcha_string( $permitted_chars, $string_length );

// Set the captcha string in the session.
$_SESSION['captcha_text'] = $captcha_string;

// Randomly place the characters on the image.
for ( $i = 0; $i < $string_length; $i++ ) {
	$letter_space = 170 / $string_length;
	$initial      = 15;
	imagettftext( $image, 24, random_int( -15, 15 ), $initial + $i * $letter_space, random_int( 25, 45 ), $textcolors[ random_int( 0, 1 ) ], $fonts[ array_rand( $fonts ) ], $captcha_string[ $i ] );
}

// Output the image, then free up the memory.
header( 'Content-type: image/png' );
imagepng( $image );
imagedestroy( $image );
