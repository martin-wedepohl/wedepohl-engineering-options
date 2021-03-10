<?php defined( 'ABSPATH' ) || die( '' ); ?>
<?php get_header(); ?>

<article id="<?php echo $post->ID; ?>" class="post-<?php echo $post->ID; ?> page type-page status-<?php echo $post->post_status; ?> <?php echo $post->post_name; ?>">

	<h1><?php the_title(); ?></h1>

	<section id="education" class="education-section">
		<h2 class="education-title">Education <span class="resume-nav">(<a href="#skills">Skills</a> | <a href="#work-experience">Work Experience</a> | <a href="#activities">Activities</a>)</span></h2>
		<?php echo do_shortcode( '[weop_education]' ); ?>
		<a href="<?php echo esc_url( get_site_url() ); ?>/resume/additional-seminars">Additional Seminars</a>
	</section>

	<section id="skills" class="skills-section">
		<h2 class="skills-title">Skills <span class="resume-nav">(<a href="#education">Education</a> | <a href="#work-experience">Work Experience</a> | <a href="#activities">Activities</a>)</span></h2>
		<?php echo do_shortcode( '[weop_skills]' ); ?>
	</section>

	<section id="work-experience" class="work-experience-section">
		<h2 class="work-experience-title">Work Experience <span class="resume-nav">(<a href="#education">Education</a> | <a href="#skills">Skills</a> | <a href="#activities">Activities</a>)</span></h2>
		<?php echo do_shortcode( '[weop_jobs]' ); ?>
	</section>

	<section id="activities" class="activities-section">
		<h2 class="activities-title">Activities <span class="resume-nav">(<a href="#education">Education</a> | <a href="#skills">Skills</a> | <a href="#work-experience">Work Experience</a>)</span></h2>
		<?php echo do_shortcode( '[weop_activities]' ); ?>
	</section>

</article>

<?php get_footer(); ?>
