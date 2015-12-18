<?php
/**
 * @package    Cherry_Framework
 * @subpackage Class
 * @author     Cherry Team <support@cherryframework.com>
 * @copyright  Copyright (c) 2012 - 2015, Cherry Team
 * @link       http://www.cherryframework.com/
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Google Map static.
 */
class cherry_google_map_static extends cherry_register_static {

	/**
	 * Callback-method for registered static.
	 *
	 * @since 4.0.0
	 */
	public function callback() {
		echo do_shortcode(cherry_get_option( 'google_map_block' ));
	}
}

/**
 * Registration for google_map static.
 */
$args = array(
	'id'      => 'google-map', // Static ID
	'name'    => __( 'Google Map', 'theme3700' ), // Static name
	'options' => array(
		'col-lg'   => 'col-lg-12',  // (optional) Column class for a large devices (≥1200px)
		'col-md'   => 'col-md-12',  // (optional) Column class for a medium devices (≥992px)
		'col-sm'   => 'col-sm-12', // (optional) Column class for a tablets (≥768px)
		'col-xs'   => 'col-xs-12', // (optional) Column class for a phones (<768px)
		'class'    => '', // (optional) Extra CSS class
		'position' => 1, // (optional) Position in static area (1 - first static, 2 - second static, etc.)
		'area'     => 'available-statics', // (required) ID for static area
		'collapse' => true, // (required) Collapse column paddings?
	)
);
new cherry_google_map_static( $args );