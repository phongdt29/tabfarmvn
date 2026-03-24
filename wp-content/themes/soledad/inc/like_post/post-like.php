<?php
/**
 * Create post like for posts
 *
 * Idea from Jon Masterson < http://jonmasterson.com/ >
 * @since 1.0
 */

add_action( 'wp_ajax_nopriv_penci-post-like', 'penci_post_like' );
add_action( 'wp_ajax_penci-post-like', 'penci_post_like' );
function penci_post_like() {
	$nonce = $_POST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
		die ( 'Nope!' );
	}

	if ( isset( $_POST['penci_post_like'] ) ) {

		$reaction = isset( $_POST['reaction'] ) ? sanitize_text_field( $_POST['reaction'] ) : 'like'; // reaction type
		$meta_key = '_post_' . $reaction . '_count'; // set meta key based on reaction type

		if ( 'undefined' == $reaction || empty( $reaction ) ) {
			$reaction = 'like';
			$meta_key = '_post_like_count';
		}

		$post_id         = $_POST['post_id']; // post id
		$post_like_count = get_post_meta( $post_id, $meta_key, true ); // post like count

		if ( function_exists( 'wp_cache_post_change' ) ) { // invalidate WP Super Cache if exists
			$GLOBALS["super_cache_enabled"] = 1;
			/** @disregard */
			wp_cache_post_change( $post_id );
		}

		if ( is_user_logged_in() ) { // user is logged in
			$user_id = get_current_user_id(); // current user

			$user_key       = '_liked_posts';
			$meta_user_key  = '_user_liked';
			$user_count_key = '_user_' . $reaction . '_count';

			if ( $reaction !== 'like' ) {
				$user_key      = '_liked_' . $reaction . '_posts';
				$meta_user_key = '_user_' . $reaction . '_liked';
			}

			$meta_POSTS  = get_user_option( $user_key, $user_id ); // post ids from user meta
			$meta_USERS  = get_post_meta( $post_id, $meta_user_key ); // user ids from post meta
			$liked_POSTS = null; // setup array variable
			$liked_USERS = null; // setup array variable

			if ( $meta_POSTS && count( $meta_POSTS ) != 0 ) { // meta exists, set up values
				$liked_POSTS = $meta_POSTS;
			}

			if ( ! is_array( $liked_POSTS ) ) // make array just in case
			{
				$liked_POSTS = array();
			}

			if ( count( $meta_USERS ) != 0 ) { // meta exists, set up values
				$liked_USERS = $meta_USERS[0];
			}

			if ( ! is_array( $liked_USERS ) ) // make array just in case
			{
				$liked_USERS = array();
			}

			$liked_POSTS[ 'post-' . $post_id ] = $post_id; // Add post id to user meta array
			$liked_USERS[ 'user-' . $user_id ] = $user_id; // add user id to post meta array
			$user_likes                        = count( $liked_POSTS ); // count user likes

			if ( ! penci_AlreadyLiked( $post_id, $reaction ) ) { // like the post
				update_post_meta( $post_id, $meta_user_key, $liked_USERS ); // Add user ID to post meta
				update_post_meta( $post_id, $meta_key, ++ $post_like_count ); // +1 count post meta
				update_user_option( $user_id, $user_key, $liked_POSTS ); // Add post ID to user meta
				update_user_option( $user_id, $user_count_key, $user_likes ); // +1 count user meta
				echo absint( $post_like_count ); // update count on front end

			} else { // unlike the post
				$pid_key = array_search( $post_id, $liked_POSTS ); // find the key
				$uid_key = array_search( $user_id, $liked_USERS ); // find the key
				unset( $liked_POSTS[ $pid_key ] ); // remove from array
				unset( $liked_USERS[ $uid_key ] ); // remove from array
				$user_likes = count( $liked_POSTS ); // recount user likes
				update_post_meta( $post_id, $meta_user_key, $liked_USERS ); // Remove user ID from post meta
				update_post_meta( $post_id, $meta_key, -- $post_like_count ); // -1 count post meta
				update_user_option( $user_id, $user_key, $liked_POSTS ); // Remove post ID from user meta
				update_user_option( $user_id, $user_count_key, $user_likes ); // -1 count user meta
				echo "already" . absint( $post_like_count ); // update count on front end

			}

		} else { // user is not logged in (anonymous)
			$ip = $_SERVER['REMOTE_ADDR']; // user IP address

			$meta_ip_meta = '_user_IP';

			if ( $reaction !== 'like' ) {
				$meta_ip_meta = '_user_' . $reaction . '_IP';
			}

			$meta_IPS  = get_post_meta( $post_id, $meta_ip_meta ); // stored IP addresses
			$liked_IPS = null; // set up array variable

			if ( count( $meta_IPS ) != 0 ) { // meta exists, set up values
				$liked_IPS = $meta_IPS[0];
			}

			if ( ! is_array( $liked_IPS ) ) // make array just in case
			{
				$liked_IPS = array();
			}

			if ( ! in_array( $ip, $liked_IPS ) ) // if IP not in array
			{
				$liked_IPS[ 'ip-' . $ip ] = $ip;
			} // add IP to array

			if ( ! penci_AlreadyLiked( $post_id ) ) { // like the post
				update_post_meta( $post_id, $meta_ip_meta, $liked_IPS ); // Add user IP to post meta
				update_post_meta( $post_id, $meta_key, ++ $post_like_count ); // +1 count post meta
				echo absint( $post_like_count ); // update count on front end

			} else { // unlike the post
				$ip_key = array_search( $ip, $liked_IPS ); // find the key
				unset( $liked_IPS[ $ip_key ] ); // remove from array
				update_post_meta( $post_id, $meta_ip_meta, $liked_IPS ); // Remove user IP from post meta
				update_post_meta( $post_id, $meta_key, -- $post_like_count ); // -1 count post meta
				echo "already" . absint( $post_like_count ); // update count on front end

			}
		}
	}

	exit;
}

/**
 * Check if user is liked post
 */
function penci_AlreadyLiked( $post_id, $reaction = 'like' ) { // test if user liked before
	if ( is_user_logged_in() ) { // user is logged in
		$user_id       = get_current_user_id(); // current user
		$meta_user_key = '_user_liked';
		if ( $reaction !== 'like' ) {
			$meta_user_key = '_user_' . $reaction . '_liked';
		}
		$meta_USERS  = get_post_meta( $post_id, $meta_user_key ); // user ids from post meta
		$liked_USERS = ""; // set up array variable

		if ( count( $meta_USERS ) != 0 ) { // meta exists, set up values
			$liked_USERS = $meta_USERS[0];
		}

		if ( ! is_array( $liked_USERS ) ) // make array just in case
		{
			$liked_USERS = array();
		}

		if ( in_array( $user_id, $liked_USERS ) ) { // True if User ID in array
			return true;
		}

		return false;

	} else { // user is anonymous, use IP address for voting

		$meta_ip_meta = '_user_IP';

		if ( $reaction !== 'like' ) {
			$meta_ip_meta = '_user_' . $reaction . '_IP';
		}

		$meta_IPS  = get_post_meta( $post_id, $meta_ip_meta ); // get previously voted IP address
		$ip        = $_SERVER["REMOTE_ADDR"]; // Retrieve current user IP
		$liked_IPS = ""; // set up array variable

		if ( count( $meta_IPS ) != 0 ) { // meta exists, set up values
			$liked_IPS = $meta_IPS[0];
		}

		if ( ! is_array( $liked_IPS ) ) // make array just in case
		{
			$liked_IPS = array();
		}

		if ( in_array( $ip, $liked_IPS ) ) { // True is IP in array
			return true;
		}

		return false;
	}

}

/**
 * Front end button like post
 */
function penci_getPostLikeLink( $post_id ) {
	if ( get_theme_mod( 'penci__hide_share_plike' ) ) {
		return;
	}

	$like_count = get_post_meta( $post_id, "_post_like_count", true ); // get post likes
	if ( ! $like_count ): $like_count = '0'; endif;

	if ( penci_AlreadyLiked( $post_id ) ) {
		$class = esc_attr( ' liked' );
		$title = esc_html__( 'Unlike', 'soledad' );
	} else {
		$class = '';
		$title = esc_html__( 'Like', 'soledad' );
	}
	$return = '<a href="#" class="penci-post-like' . $class . '" aria-label="Like this post" data-post_id="' . $post_id . '" title="' . $title . '" data-like="' . esc_html__( 'Like', 'soledad' ) . '" data-unlike="' . esc_html__( 'Unlike', 'soledad' ) . '">' . penci_icon_by_ver( 'far fa-heart' ) . '<span class="dt-share">' . sanitize_text_field( $like_count ) . '</span></a>';

	return $return;
}

/**
 * Front end button like post in single
 */
function penci_single_getPostLikeLink( $post_id ) {
	$like_count = get_post_meta( $post_id, "_post_like_count", true ); // get post likes
	if ( ! $like_count ): $like_count = '0'; endif;

	if ( penci_AlreadyLiked( $post_id ) ) {
		$class = esc_attr( ' liked' );
		$title = esc_html__( 'Unlike', 'soledad' );
	} else {
		$class = '';
		$title = esc_html__( 'Like', 'soledad' );
	}
	$return = '<span class="count-number-like">' . sanitize_text_field( $like_count ) . '</span><a href="#" aria-label="Like this post" class="penci-post-like single-like-button' . $class . '" data-post_id="' . $post_id . '" title="' . $title . '" data-like="' . esc_html__( 'Like', 'soledad' ) . '" data-unlike="' . esc_html__( 'Unlike', 'soledad' ) . '">' . penci_icon_by_ver( 'far fa-heart' ) . '</a>';

	return $return;
}

/**
 * Front end count like post
 */
function penci_getPostCountLike( $post_id, $type = '' ) {
	$meta_key   = $type ? '_post_' . $type . '_count' : '_post_like_count'; // set meta key based on type
	$like_count = get_post_meta( $post_id, $meta_key, true ); // get post likes
	$count      = ( ! isset( $like_count ) || empty( $like_count ) ) ? '0' : $like_count;

	return $count;
}

add_action( 'penci_action_after_the_content', function () {
	if ( get_theme_mod( 'penci_post_reactions_enable' ) ) {
		get_template_part( 'template-parts/single-reactions' );
	}
} );

add_action( 'rest_api_init', function () {
	register_rest_route(
		'penci/v1',
		'/reactions/(?P<id>\d+)',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'penci_rest_get_reactions',
			'permission_callback' => '__return_true', // public
			'args'                => [
				'id' => [
                    'validate_callback' => function ( $param ) {
                        return is_numeric( $param );
                    },
                ],
			],
		]
	);
} );

function penci_rest_get_reactions( WP_REST_Request $request ) {

	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_REST_Response(
			[ 'message' => 'Invalid nonce' . $nonce ],
			403
		);
	}

	$post_id = (int) $request['id'];
	if ( ! $post_id ) {
		return new WP_REST_Response(
			[ 'message' => 'Invalid post ID' ],
			400
		);
	}

	$cache_key = 'penci_reacts_' . $post_id;

	// Object cache first (Redis / Memcached)
	$reactions_data = wp_cache_get( $cache_key, 'penci' );

	if ( false === $reactions_data ) {

		// ONE query, meta cache aware
		$all_meta = get_post_meta( $post_id );

		$reactions      = [ 'like', 'love', 'haha', 'wow', 'sad', 'angry' ];
		$reactions_data = [];

		foreach ( $reactions as $reaction ) {
			$key                         = '_post_' . $reaction . '_count';
			$reactions_data[ $reaction ] = isset( $all_meta[ $key ][0] )
				? (int) $all_meta[ $key ][0]
				: 0;
		}

		// Cache for 5 minutes
		wp_cache_set( $cache_key, $reactions_data, 'penci', 300 );
	}

	return rest_ensure_response( $reactions_data );
}

add_action( 'updated_post_meta', 'penci_clear_reactions_cache', 10, 4 );
function penci_clear_reactions_cache( $meta_id, $post_id, $meta_key, $meta_value ) {

    if ( strpos( $meta_key, '_post_' ) !== 0 || strpos( $meta_key, '_count' ) === false ) {
        return;
    }

    wp_cache_delete( 'penci_reacts_' . $post_id, 'penci' );
}