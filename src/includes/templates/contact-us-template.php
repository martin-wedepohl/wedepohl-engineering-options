<?php
/**
 * Contact Us Template
 *
 * PHP Version 7
 *
 * @category WEOP
 * @package  Template
 * @author   Martin Wedepohl <martin@wedepohlengineering.com>
 * @license  GPL3 or later
 */

defined( 'ABSPATH' ) || die( '' );

?>
<?php get_header(); ?>

<article id="<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?> page type-page status-<?php echo $post->post_status; ?> <?php echo $post->post_name; ?>">

	<h1><?php the_title(); ?></h1>

	<?php the_content(); ?>

	<?php $weop_contact->display_form(); ?>

</article>

<?php get_footer();
