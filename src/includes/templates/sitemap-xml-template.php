<?php

/**
 * Get all the pages in the website to compile a sitemap.xml
 */
$sitemap    = \get_page_by_path( 'sitemap-xml' );
$sitemap_id = $sitemap->ID;
$file_path  = plugin_dir_url( __FILE__ );
$args       = array(
	'sort_order'  => 'DESC',
	'sort_column' => 'post_modified',
	'exclude'     => array( $sitemap_id ),
);
$all_pages  = get_pages( $args );

/**
 * Create the timezone string
 */
if ( str_replace( '-', '', get_option( 'gmt_offset' ) ) < 10 ) {
	$tempo = '-0' . str_replace( '-', '', get_option( 'gmt_offset' ) );
} else {
	$tempo = get_option( 'gmt_offset' );
}
if ( 3 === strlen( $tempo ) ) {
	$tempo = $tempo . ':00';
}

/**
 * Create the sitemap.xml page.
 * Use the sitemap.xsl style sheet to format the page when viewed.
 */
$xml_string  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml_string .= "<?xml-stylesheet type=\"text/xsl\" href=\"{$file_path}sitemap.xsl\"?>\n";
$xml_string .= "<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
foreach ( $all_pages as $the_page ) {
	$frequency = 'monthly';
	if ( 'resume' === $the_page->post_name || 'additional-seminars' === $the_page->post_name || 'projects' === $the_page->post_name || 'projects' === $the_page->post_name ) {
		$frequency = 'always';
	}
	if ( 'contact-us' === $the_page->post_name ) {
		$frequency = 'yearly';
	}
	$priority = '0.5';
	if ( 'resume' === $the_page->post_name || 'additional-seminars' === $the_page->post_name || 'projects' === $the_page->post_name || 'projects' === $the_page->post_name ) {
		$priority = '0.8';
	}
	if ( 'home' === $the_page->post_name ) {
		$priority = '1.0';
	}
	$parts       = explode( ' ', $the_page->post_modified );
	$permalink   = esc_url( get_permalink( $the_page->ID ) );
	$xml_string .= "\t<url>\n";
	$xml_string .= "\t\t<loc>{$permalink}</loc>\n";
	$xml_string .= "\t\t<lastmod>{$parts[0]}T{$parts[1]}{$tempo}</lastmod>\n";
	$xml_string .= "\t\t<changefreq>{$frequency}</changefreq>\n";
	$xml_string .= "\t\t<priority>{$priority}</priority>\n";
	$xml_string .= "\t</url>\n";
}
$xml_string .= '</urlset>';
header( 'Content-Type: application/xml' );
echo $xml_string;
die();
