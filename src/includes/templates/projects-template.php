<?php defined( 'ABSPATH' ) || die( '' ); ?>
<?php get_header(); ?>

<article id="<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?> page type-page status-<?php echo $post->post_status; ?> <?php echo $post->post_name; ?>">

	<h1><?php the_title(); ?></h1>

	<section id="projects" class="projects-section">
		<?php echo do_shortcode( '[weop_projects]' ); ?>
	</section>

</article>

<?php get_footer(); ?>
