<?php
/* ------------------------------------------------------- */
/* Date
/* ------------------------------------------------------- */
if ( ! function_exists( 'penci_penci_date_shortcode' ) ) {
	function penci_penci_date_shortcode( $atts, $content ) {
		extract( shortcode_atts( array(
			'format' => 'l, F j Y'
		), $atts ) );

		$return = '';
		// Define allowed date format characters (from PHP date())
		$allowed_format_chars = 'dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU'; 

		if ( empty( $format ) ) {
			$format = 'l, F j Y';
		} else {
			// Strip out anything not in allowed characters
			//$format = preg_replace( '/[^' . preg_quote( $allowed_format_chars, '/' ) . ']/', '', $format );

			// Fallback if nothing valid left
			if ( empty( $format ) ) {
				$format = 'l, F j Y';
			}
		}

		if ( function_exists( 'wp_date' ) ) {
			if ( get_theme_mod( 'penci_time_sync' ) ) {
				$return = '<span data-format="' . esc_attr( $format ) . '" class="penci-dtf-real">' . wp_date( $format ) . '</span>';
			} else {
				$return = wp_date( $format );
			}
		}

		return $return;
	}
}