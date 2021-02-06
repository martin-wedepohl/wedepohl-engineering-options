<?php get_header(); ?>

<article id="<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?> page type-page status-<?php echo $post->post_status; ?> hentry entry <?php echo $post->post_name; ?>">
	<div class="entry-content">
		<h1><?php the_title(); ?></h1>

		<section id="additional-seminars" class="seminars-section">
			<?php echo do_shortcode( '[weop_education show_seminars="true"]' ); ?>
		</section>
	</div>
</article>

<?php get_footer(); ?>
