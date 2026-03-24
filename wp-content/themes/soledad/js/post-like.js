jQuery( document ).ready( function ($) {
	var penci_format_to_k = function ( num ) {
		if ( num > 999 && num < 1000000 ) {
			return ( num / 1000 ).toFixed( 1 ) + 'K';
		}
		if ( num >= 1000000 ) {
			return ( num / 1000000 ).toFixed( 1 ) + 'M';
		}
		return num;
	}

	// Helper functions for localStorage management
	var penci_like_storage = {
		getKey: function( post_id, reaction ) {
			reaction = reaction || 'like'; // Default to 'like' if undefined
			return 'penci_like_' + post_id + '_' + reaction;
		},
		isLiked: function( post_id, reaction ) {
			var key = this.getKey( post_id, reaction );
			return localStorage.getItem( key ) === 'liked';
		},
		isUnLiked: function( post_id, reaction ) {
			var key = this.getKey( post_id, reaction );
			return localStorage.getItem( key ) !== 'liked';
		},
		setLiked: function( post_id, reaction, liked ) {
			var key = this.getKey( post_id, reaction );
			if ( liked ) {
				localStorage.setItem( key, 'liked' );
			} else {
				localStorage.setItem( key, 'unliked' );
			}
		}
	};

	// Restore like status on page load
	var penci_restore_like_status = function() {
		$( '.penci-post-like' ).each( function() {
			var $this = $( this ),
				post_id = $this.data( 'post_id' ),
				reaction = $this.data( 'reaction' ) || 'like';

			if ( post_id && reaction && penci_like_storage.isLiked( post_id, reaction ) ) {
				$this.addClass( 'liked' );
				$this.prop( 'title', 'Unlike' );
			}

			if ( post_id && reaction && penci_like_storage.isUnLiked( post_id, reaction ) ) {
				$this.removeClass( 'liked' );
				$this.prop( 'title', 'Like' );
			}
		} );
	};

	// Restore like status when document is ready
	penci_restore_like_status();

	jQuery( 'body' ).on( 'click', '.penci-post-like', function ( event ) {
		event.preventDefault();
		var $this = jQuery( this ),
			post_id = $this.data( "post_id" ),
			reaction = $this.data( "reaction" ) || 'like', // Default to 'like' if undefined
			like_text = $this.data( "like" ),
			unlike_text = $this.data( "unlike" ),
			$selector = $this.children('.dt-share' );
			if( $this.hasClass( 'single-like-button' ) ) {
				$selector = $this.parent().find('.count-number-like');
			}
			if ( $this.hasClass( 'penci-post-reaction' ) ) {
				$selector = $this.closest( '.penci-post-reaction-item' ).find( '.dt-share' );
			}
		var	$like = parseInt( $selector.text() );

		if ( $this.hasClass( 'single-like-button' ) ) {
			$selector = $( '.post-share-plike .count-number-like' );
			$this = $( '.single-like-button' );
		}

		var is_liked = $this.hasClass( 'liked' );

		if ( is_liked ) {
			$this.removeClass( 'liked' );
			$this.prop( 'title', 'Like' );
			$selector.html( ( $like - 1 ) );
			penci_like_storage.setLiked( post_id, reaction, false );
		}
		else {
			$this.addClass( 'liked' );
			$this.prop( 'title', 'Unlike' );
			$selector.html( ( $like + 1 ) );
			penci_like_storage.setLiked( post_id, reaction, true );
		}

		jQuery.ajax( {
			type: "post",
			url : ajax_var_more.url,
			data: "action=penci-post-like&nonce=" + ajax_var_more.nonce + "&penci_post_like=&post_id=" + post_id + "&reaction=" + reaction,
		} );
	} );

	var $tagsShareBox = jQuery('.tags-share-box');
	var post_id = $tagsShareBox.data('id');
	var hasPostSharePlike = $tagsShareBox.find('.post-share-plike').length > 0;
	var hasPenciPostReactions = jQuery('.penci-post-reactions').length > 0;

	if ( typeof post_id !== 'undefined' && (hasPostSharePlike || hasPenciPostReactions) ) {

		fetch(
			ajax_var_more.rest_url + 'penci/v1/reactions/' + post_id,
			{
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'X-WP-Nonce': ajax_var_more.wp_rest
				}
			}
		)
		.then(function (response) {
			return response.json();
		})
		.then(function (reacts) {

			jQuery.each( reacts, function( reaction, count ) {
				var $like_btn = jQuery('.penci-reaction-count[data-reaction="' + reaction + '"]');
				if ( $like_btn.length ) {
					$like_btn.text( penci_format_to_k( count ) );
				}
			});

			if ( jQuery('.count-number-like').length ) {
				var total_likes = reacts.like ? reacts.like : 0;
				jQuery('.count-number-like').text( penci_format_to_k( total_likes ) );
			}

		})
		.catch(function (error) {
			console.error('Penci reactions error:', error);
		});
	}
} );