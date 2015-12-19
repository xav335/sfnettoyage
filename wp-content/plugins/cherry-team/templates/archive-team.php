<?php
/**
 * The Template for displaying archive CPT Team.
 */

global $wp_query;

$args = array(
	'template'     => 'default.tmpl',
	'custom_class' => 'team-listing row',
	'item_class'   => 'team-listing_item',
	'container'    => false,
	'col_xs'       => '12',
	'col_sm'       => '6',
	'col_md'       => '4',
	'col_lg'       => false,
	'size'         => 'thumbnail',
	'pager'        => true,
	'limit'        => Cherry_Team_Templater::$posts_per_archive_page,
	'group'        => !empty( $wp_query->query_vars['term'] ) ? $wp_query->query_vars['term'] : '',
);
$data = new Cherry_Team_Data;
$data->the_team( $args );