<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;

$posts_table = $wpdb->prefix . 'posts';
$meta_table  = $wpdb->prefix . 'postmeta';

$custom_posts = $wpdb->get_results( "SELECT DISTINCT ID FROM {$posts_table} WHERE `post_type` = 'activities'" );
if ( count( $custom_posts ) ) {
	foreach ( $custom_posts as $custom_post ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$meta_table} WHERE `post_id` = %d", $custom_post->ID ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$posts_table} WHERE `ID` = %d", $custom_post->ID ) );
	}
}

$custom_posts = $wpdb->get_results( "SELECT DISTINCT ID FROM {$posts_table} WHERE `post_type` = 'education'" );
if ( count( $custom_posts ) ) {
	foreach ( $custom_posts as $custom_post ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$meta_table} WHERE `post_id` = %d", $custom_post->ID ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$posts_table} WHERE `ID` = %d", $custom_post->ID ) );
	}
}

$custom_posts = $wpdb->get_results( "SELECT DISTINCT ID FROM {$posts_table} WHERE `post_type` = 'jobs'" );
if ( count( $custom_posts ) ) {
	foreach ( $custom_posts as $custom_post ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$meta_table} WHERE `post_id` = %d", $custom_post->ID ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$posts_table} WHERE `ID` = %d", $custom_post->ID ) );
	}
}

$custom_posts = $wpdb->get_results( "SELECT DISTINCT ID FROM {$posts_table} WHERE `post_type` = 'plugins'" );
if ( count( $custom_posts ) ) {
	foreach ( $custom_posts as $custom_post ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$meta_table} WHERE `post_id` = %d", $custom_post->ID ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$posts_table} WHERE `ID` = %d", $custom_post->ID ) );
	}
}

$custom_posts = $wpdb->get_results( "SELECT DISTINCT ID FROM {$posts_table} WHERE `post_type` = 'projects'" );
if ( count( $custom_posts ) ) {
	foreach ( $custom_posts as $custom_post ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$meta_table} WHERE `post_id` = %d", $custom_post->ID ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$posts_table} WHERE `ID` = %d", $custom_post->ID ) );
	}
}

$custom_posts = $wpdb->get_results( "SELECT DISTINCT ID FROM {$posts_table} WHERE `post_type` = 'skills'" );
if ( count( $custom_posts ) ) {
	foreach ( $custom_posts as $custom_post ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$meta_table} WHERE `post_id` = %d", $custom_post->ID ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$posts_table} WHERE `ID` = %d", $custom_post->ID ) );
	}
}

delete_option('weop_options');
