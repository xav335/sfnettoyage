<?php
/**
 * Cherry Instagram Widget.
 *
 * @package   Cherry_Social
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @link      http://www.cherryframework.com/
 * @copyright 2015 Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Cherry_Instagram' ) ) {
	class Cherry_Instagram extends WP_Widget {

		/**
		 * Unique identifier for widget.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		protected $widget_slug = 'cherry-instagram';

		/**
		 * Specifies the classname and description.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			parent::__construct(
				$this->get_widget_slug(),
				__( 'Cherry Instagram', 'cherry-social' ),
				array(
					'classname'   => $this->get_widget_slug() . '_widget',
					'description' => __( 'A widget for Instagram.', 'cherry-social' )
				)
			);

			// Refreshing the widget's cached output with each new post.
			add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
		}

		/**
		 * Return the widget slug.
		 *
		 * @since  1.0.0
		 * @return Plugin slug variable.
		 */
		public function get_widget_slug() {
			return $this->widget_slug;
		}

		/**
		 * Outputs the content of the widget.
		 *
		 * @since 1.0.0
		 * @param array $args     The array of form elements.
		 * @param array $instance The current instance of the widget.
		 */
		public function widget( $args, $instance ) {

			// Check if there is a cached output.
			$cache = wp_cache_get( $this->get_widget_slug(), 'widget' );

			if ( !is_array( $cache ) ) {
				$cache = array();
			}

			if ( !isset( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}

			if ( isset( $cache[ $args['widget_id'] ] ) ) {
				return print $cache[ $args['widget_id'] ];
			}

			extract( $args, EXTR_SKIP );

			$client_id     = esc_attr( $instance['client_id'] );
			$user_name     = !empty( $instance['user_name'] )     ? strtolower( trim( $instance['user_name'] ) ) : '';
			$tag           = !empty( $instance['tag'] )           ? esc_attr( $instance['tag'] ) : '';
			$image_counter = !empty( $instance['image_counter'] ) ? absint( $instance['image_counter'] ) : '';
			$button_text   = !empty( $instance['button_text'] )   ? esc_attr( $instance['button_text'] ) : '';

			$endpoints  = ( !empty( $instance['endpoints'] ) && in_array( $instance['endpoints'], array_keys( $this->get_endpoints_options() ) ) ) ? $instance['endpoints'] : '';
			$image_size = ( !empty( $instance['image_size'] ) && in_array( $instance['image_size'], array_keys( $this->get_image_size_options() ) ) ) ? $instance['image_size'] : '';

			if ( 'hashtag' == $endpoints ) {
				if ( empty( $tag ) ) {
					return print $before_widget . __( 'Please, enter #hashtag.', 'cherry-social' ) . $after_widget;
				}
			}

			if ( 'self' == $endpoints ) {
				if ( empty( $user_name ) ) {
					return print $before_widget . __( 'Please, enter your username.', 'cherry-social' ) . $after_widget;
				}
			}

			if ( empty( $client_id ) ) {
				return print $before_widget . __( 'Please, enter your Instagram CLIENT ID.', 'cherry-social' ) . $after_widget;
			}

			if ( $image_counter <= 0 ) {
				return '';
			}

			$config = array();

			if ( ! empty( $instance['link'] ) ) $config[] = 'link';
			if ( ! empty( $instance['display_time'] ) ) $config[] = 'time';
			if ( ! empty( $instance['display_description'] ) ) $config[] = 'description';
			if ( ! empty( $image_size ) ) $config['thumb'] = $image_size;

			if ( 'self' == $endpoints ) {
				$user_id = $this->get_user_id( $user_name, $client_id );

				if ( ! $user_id ) {
					return print $before_widget . __( 'Please, enter a valid username and CLIENT ID.', 'cherry-social' ) . $after_widget;
				}

				$data = $user_id;

			} else {
				$data = $tag;
			}

			$config['endpoints'] = $endpoints;
			$photos = $this->get_photos( $data, $client_id, $image_counter, $config );

			if ( ! $photos ) {
				return print $before_widget . __( 'Please, enter a valid CLIENT ID.', 'cherry-social' ) . $after_widget;
			}

			$output = $before_widget;

			/**
			 * Fires before a content widget.
			 *
			 * @since 1.0.0
			 */
			do_action( $this->widget_slug . '_before', $args, $instance );

			if ( !empty( $instance['title'] ) ) {
				/**
				 * Filter the widget title.
				 *
				 * @since 1.0.0
				 * @param string $title       The widget title.
				 * @param array  $instance    An array of the widget's settings.
				 * @param mixed  $widget_slug The widget ID.
				 */
				$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->widget_slug );

				// Display the widget title if one was input.
				if ( $title ) {
					$output .= $before_title;
					$output .= $title;
					$output .= $after_title;
				}
			}

			$date_format = get_option( 'date_format' );

			$output .= '<div class="cherry-instagram_items">';

			foreach ( (array) $photos as $key => $photo ) {

				$desc = in_array( 'description', $config ) ? $photo['description'] : '';
				$output .= '<div class="cherry-instagram_item">';

					$output .= '<div class="cherry-instagram_thumbnail ' . sanitize_html_class( $image_size ) . '">';
						$output .= in_array( 'link', $config ) ? '<a class="cherry-instagram_link" href="' . esc_url( $photo['link'] ) . '" target="_blank">' : '';
							$output .= '<img src="' . esc_url( $photo['thumb'] ) . '" alt="' . esc_attr( $desc ) . '">';
						$output .= in_array( 'link', $config ) ? '</a>' : '';
					$output .= '</div>';

					$wrap = in_array( 'time', $config ) || in_array( 'description', $config ) ? '<div class="cherry-instagram_caption caption">%s</div>' : '%s';

						$_output = in_array( 'time', $config ) ? '<time datetime="' . esc_attr( date( 'Y-m-d\TH:i:sP' ), $photo['time'] ) . '" class="cherry-instagram_date">' . date( $date_format, $photo['time'] ) . '</time>' : '';

						$_output .= in_array( 'description', $config ) ? '<div class="cherry-instagram_desc">' . $photo['description'] . '</div>' : '';

					$output .= sprintf( $wrap, $_output );

				$output .= '</div>';
			}

			$output .= '</div>';

			$btn_link = ( 'self' == $endpoints ) ? '//instagram.com/' . $user_name : '//instagram.com/explore/tags/' . $tag;

			$output .= $button_text ? '<a href="' . esc_url( $btn_link ) . '" class="btn btn-primary" role="button" target="_blank">' . $button_text . '</a>' : '';

			/**
			 * Fires after a content widget.
			 *
			 * @since 1.0.0
			 */
			do_action( $this->widget_slug . '_after', $args, $instance );

			$output .= $after_widget;

			$cache[ $args['widget_id'] ] = $output;
			wp_cache_set( $this->get_widget_slug(), $cache, 'widget' );

			print $output;
		}

		public function flush_widget_cache() {
			wp_cache_delete( $this->get_widget_slug(), 'widget' );
		}

		/**
		 * Processes the widget's options to be saved.
		 *
		 * @since 1.0.0
		 * @param array new_instance The new instance of values to be generated via the update.
		 * @param array old_instance The previous instance of values before the update.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']         = strip_tags( $new_instance['title'] );
			$instance['tag']           = trim( $new_instance['tag'], '#' );
			$instance['user_name']     = esc_attr( $new_instance['user_name'] );
			$instance['client_id']     = esc_attr( $new_instance['client_id'] );
			$instance['button_text']   = esc_attr( $new_instance['button_text'] );
			$instance['endpoints']     = esc_attr( $new_instance['endpoints'] );
			$instance['image_size']    = esc_attr( $new_instance['image_size'] );
			$instance['image_counter'] = absint( $new_instance['image_counter'] );

			$instance['display_description'] = !empty( $new_instance['display_description'] ) ? 1 : 0;
			$instance['display_time']        = !empty( $new_instance['display_time'] ) ? 1 : 0;
			$instance['link']                = !empty( $new_instance['link'] ) ? 1 : 0;

			// Delete a cache.
			delete_transient( 'cherry_instagram_user_id' );
			delete_transient( 'cherry_instagram_photos' );

			return $instance;
		}

		/**
		 * Generates the administration form for the widget.
		 *
		 * @since 1.0.0
		 * @param array instance The array of keys and values for the widget.
		 */
		public function form( $instance ) {
			/**
			 * Filters default widget settings.
			 *
			 * @since 1.0.0
			 * @param array
			 */
			$defaults = array(
				'title'               => '',
				'endpoints'           => 'hashtag', // hashtag or self
				'user_name'           => '',
				'tag'                 => '',
				'client_id'           => '',
				'image_counter'       => 4,
				'image_size'          => 'thumbnail',
				'display_description' => 0,
				'display_time'        => 0,
				'link'                => 1,
				'button_text'         => '',
			);

			// Input (string)
			$instance    = wp_parse_args( (array) $instance, $defaults );
			$title       = esc_attr( $instance['title'] );
			$user_name   = esc_attr( $instance['user_name'] );
			$tag         = esc_attr( $instance['tag'] );
			$client_id   = esc_attr( $instance['client_id'] );
			$button_text = esc_attr( $instance['button_text'] );

			// Input (number)
			$image_counter = ! empty( $instance['image_counter'] ) ? intval( $instance['image_counter'] ) : esc_attr( $defaults['image_counter'] );

			// Select
			$endpoints  = $this->get_endpoints_options();
			$image_size = $this->get_image_size_options();

			// Checkbox
			$display_description = (bool) $instance['display_description'];
			$display_time        = (bool) $instance['display_time'];
			$link                = (bool) $instance['link'];

			// Display the admin form.
			include( trailingslashit( CHERRY_SOCIAL_ADMIN ) . 'views/instagram-admin.php' );
		}

		/**
		 * Get an array of the available endpoints options.
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_endpoints_options() {
			return apply_filters( 'cherry_instagram_get_endpoints_options', array(
				'self'    => __( 'My Photos', 'cherry-social' ),
				'hashtag' => __( 'Tagged photos', 'cherry-social' ),
			) );
		}

		/**
		 * Get an array of the available endpoints options.
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_image_size_options() {
			return apply_filters( 'cherry_instagram_get_image_size_options', array(
				'large'     => __( 'Large', 'cherry-social' ),
				'thumbnail' => __( 'Thumbnail', 'cherry-social' ),
			) );
		}

		public function get_user_id( $user_name, $client_id ) {
			$cached = get_transient( 'cherry_instagram_user_id' );

			if ( false !== $cached ) {
				return $cached;
			}

			$url = add_query_arg(
				array( 'q' => esc_attr( $user_name ), 'client_id' => esc_attr( $client_id ) ),
				'https://api.instagram.com/v1/users/search/'
			);
			$response = wp_remote_get( $url );

			if ( is_wp_error( $response ) || empty( $response ) || $response ['response']['code'] != '200' ) {
				set_transient( 'cherry_instagram_user_id', false, HOUR_IN_SECONDS );
				return false;
			}

			$result  = json_decode( wp_remote_retrieve_body( $response ), true );
			$user_id = false;

			foreach ( $result['data'] as $key => $data ) {

				if ( $user_name != $data['username'] ) {
					continue;
				}

				$user_id = $data['id'];
			}

			set_transient( 'cherry_instagram_user_id', $user_id, HOUR_IN_SECONDS );

			return $user_id;
		}

		public function get_photos( $data, $client_id, $img_counter, $config ) {
			$cached = get_transient( 'cherry_instagram_photos' );

			if ( false !== $cached ) {
				return $cached;
			}

			if ( 'self' == $config['endpoints'] ) {
				$old_url = 'https://api.instagram.com/v1/users/' . $data . '/media/recent/';
			} else {
				$old_url = 'https://api.instagram.com/v1/tags/' . $data . '/media/recent/';
			}

			$url = add_query_arg(
				array( 'client_id' => esc_attr( $client_id ) ),
				$old_url
			);

			$response = wp_remote_get( $url );

			if ( is_wp_error( $response ) || empty( $response ) || $response ['response']['code'] != '200' ) {
				set_transient( 'cherry_instagram_photos', false, HOUR_IN_SECONDS );
				return false;
			}

			$result  = json_decode( wp_remote_retrieve_body( $response ), true );
			$photos  = array();
			$counter = 1;

			foreach ( $result['data'] as $photo ) {

				if ( $counter > $img_counter ) {
					break;
				}

				if ( 'image' != $photo['type'] ) {
					continue;
				}

				$_photo = array();

				if ( in_array( 'link', $config ) )
					$_photo = array_merge( $_photo, array( 'link' => esc_url( $photo['link'] ) ) );

				if ( in_array( 'time', $config ) )
					$_photo = array_merge( $_photo, array( 'time' => sanitize_text_field( $photo['created_time'] ) ) );

				if ( in_array( 'description', $config ) )
					$_photo = array_merge( $_photo, array( 'description' => wp_trim_words( $photo['caption']['text'], 3 ) ) );

				if ( array_key_exists( 'thumb', $config ) ) {
					$size   = ( 'large' == $config['thumb'] ) ? 'standard_resolution' : 'thumbnail';
					$_photo = array_merge( $_photo, array( 'thumb' => $photo['images'][ $size ]['url'] ) );
				}

				if ( ! empty( $_photo ) ) {
					array_push( $photos, $_photo );
				}

				$counter++;
			}

			set_transient( 'cherry_instagram_photos', $photos, HOUR_IN_SECONDS );

			return $photos;
		}

	}
}

function cherry_instagram_register_widget() {
	register_widget( 'Cherry_Instagram' );
}

add_action( 'widgets_init', 'cherry_instagram_register_widget' );