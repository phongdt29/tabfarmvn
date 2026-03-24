<?php

if ( ! class_exists( 'Penci_Soledad_Demo_Importer_Helper' ) ):
	class Penci_Soledad_Demo_Importer_Helper {

	}
endif;

if ( ! function_exists( 'penci_get_page_by_title' ) ) {
	function penci_get_page_by_title( $pages ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'page',
				'title'                  => esc_attr( $pages ),
				'post_status'            => 'all',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			)
		);

		if ( ! empty( $query->post ) ) {
			$page_got_by_title = $query->post;
		} else {
			$page_got_by_title = null;
		}

		return $page_got_by_title;
	}
}
if ( ! function_exists( 'penci_replace_demo_images_in_content' ) ) {
	/**
	 * Replace demo image URLs inside post_content with placeholder images
	 * preserving dimensions if possible.
	 */
	function penci_replace_demo_images_in_content( $content ) {
		$hosts = array(
			'soledad.pencidesign.net',
			'demosoledad.pencidesign.net',
			'soledaddemo.pencidesign.net',
		);

		// Template for placeholder (use sprintf)
		$placeholder_template = 'https://placehold.jp/30/ccc/000/%dx%d.png?text=Soledad+Placeholder+Image';

		// 1) Replace <img ...> tags (covers caption shortcode inner images)
		$content = preg_replace_callback( '/<img\b[^>]*>/i', function ( $match ) use ( $hosts, $placeholder_template ) {
			$img_tag = $match[0];

			// Extract src (supports quoted and unquoted)
			$src = null;
			if ( preg_match( '/\bsrc=(["\'])(.*?)\1/i', $img_tag, $m ) ) {
				$src = $m[2];
			} elseif ( preg_match( '/\bsrc=([^\s>]+)/i', $img_tag, $m ) ) {
				$src = trim( $m[1], "\"'" );
			}
			if ( empty( $src ) ) {
				return $img_tag;
			}

			// Only operate on demo hosts
			$is_demo = false;
			foreach ( $hosts as $host ) {
				if ( stripos( $src, $host ) !== false ) {
					$is_demo = true;
					break;
				}
			}
			if ( ! $is_demo ) {
				return $img_tag;
			}

			// 1) Try width/height attributes
			$width  = null;
			$height = null;
			if ( preg_match( '/\bwidth=(["\']?)(\d+)\1/i', $img_tag, $m ) ) {
				$width = (int) $m[2];
			}
			if ( preg_match( '/\bheight=(["\']?)(\d+)\1/i', $img_tag, $m ) ) {
				$height = (int) $m[2];
			}

			// 2) Try filename pattern: image-800x600.jpg
			if ( ( ! $width || ! $height ) && preg_match( '/(\d+)[xX](\d+)\.(?:jpg|jpeg|png|gif|webp|svg)/i', $src, $m ) ) {
				$width  = (int) $m[1];
				$height = (int) $m[2];
			}

			// 3) Try remote getimagesize() (may be slow; suppressed warnings)
			if ( ( ! $width || ! $height ) && filter_var( $src, FILTER_VALIDATE_URL ) ) {
				$img_size = @getimagesize( $src );
				if ( $img_size && isset( $img_size[0], $img_size[1] ) ) {
					$width  = (int) $img_size[0];
					$height = (int) $img_size[1];
				}
			}

			// 4) Fallback default
			if ( ! $width ) {
				$width = 1920;
			}
			if ( ! $height ) {
				$height = 1080;
			}

			$placeholder = sprintf( $placeholder_template, $width, $height );

			// Replace src attribute (quoted or unquoted)
			if ( preg_match( '/\bsrc=(["\'])(.*?)\1/i', $img_tag ) ) {
				$img_tag = preg_replace( '/\bsrc=(["\'])(.*?)\1/i', 'src="' . $placeholder . '"', $img_tag, 1 );
			} else {
				$img_tag = preg_replace( '/\bsrc=([^\s>]+)/i', 'src="' . $placeholder . '"', $img_tag, 1 );
			}

			// Replace/remove srcset (set a simple placeholder srcset)
			$img_tag = preg_replace( '/\s+srcset=(["\'])(.*?)\1/i', ' srcset="' . $placeholder . ' 1x"', $img_tag );

			// Replace common lazy/load attributes that might hold the original URL
			$img_tag = preg_replace( '/\bdata-(?:src|lazy|lazy-src|original)=([\"\'])(.*?)\1/i', 'data-src="' . $placeholder . '"', $img_tag );
			$img_tag = preg_replace( '/\bdata-(?:srcset)=([\"\'])(.*?)\1/i', 'data-srcset="' . $placeholder . '"', $img_tag );

			return $img_tag;
		}, $content );

		// 2) Replace any plain image URLs elsewhere (Elementor JSON blobs, inline CSS url(...), etc.)
		foreach ( $hosts as $host ) {
			$host_esc = preg_quote( $host, '#' );
			$pattern  = '#https?://(?:www\.)?' . $host_esc . '/[^\s\'"<>]+?\.(?:jpg|jpeg|png|gif|webp|svg)(?:\?[^\s\'"<>]*)?#i';

			$content = preg_replace_callback( $pattern, function ( $m ) use ( $placeholder_template ) {
				$url = $m[0];

				// dimensions detection: filename -> getimagesize -> fallback
				$width  = null;
				$height = null;

				if ( preg_match( '/(\d+)[xX](\d+)\.(?:jpg|jpeg|png|gif|webp|svg)/i', $url, $md ) ) {
					$width  = (int) $md[1];
					$height = (int) $md[2];
				} elseif ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
					$img_size = @getimagesize( $url );
					if ( $img_size && isset( $img_size[0], $img_size[1] ) ) {
						$width  = (int) $img_size[0];
						$height = (int) $img_size[1];
					}
				}

				if ( ! $width ) {
					$width = 1920;
				}
				if ( ! $height ) {
					$height = 1080;
				}

				return sprintf( $placeholder_template, $width, $height );
			}, $content );
		}

		// 3) Replace JSON-escaped URLs (e.g. "https:\/\/soledaddemo...\/img.jpg")
		$escaped_hosts   = array_map( function ( $h ) {
			return preg_quote( $h, '#' );
		}, $hosts );
		$pattern_escaped = '#https?:\\\\/\\\\/(?:www\\\\\.)?(?:' . implode( '|', $escaped_hosts ) . ')\\\\/[^\\\\\'"]+?\\\\\.(?:jpg|jpeg|png|gif|webp|svg)(?:\\\\\\?[^\\\\\'"]*)?#i';

		$content = preg_replace_callback( $pattern_escaped, function ( $m ) use ( $placeholder_template ) {
			$escaped_url = $m[0];
			$url         = str_replace( '\\/', '/', $escaped_url ); // unescape to process

			// detect dims
			$width  = null;
			$height = null;
			if ( preg_match( '/(\d+)[xX](\d+)\.(?:jpg|jpeg|png|gif|webp|svg)/i', $url, $md ) ) {
				$width  = (int) $md[1];
				$height = (int) $md[2];
			} elseif ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
				$img_size = @getimagesize( $url );
				if ( $img_size && isset( $img_size[0], $img_size[1] ) ) {
					$width  = (int) $img_size[0];
					$height = (int) $img_size[1];
				}
			}
			if ( ! $width ) {
				$width = 1920;
			}
			if ( ! $height ) {
				$height = 1080;
			}

			$placeholder = sprintf( $placeholder_template, $width, $height );

			// return escaped placeholder to match original style
			return str_replace( '/', '\/', $placeholder );
		}, $content );

		return $content;
	}
}