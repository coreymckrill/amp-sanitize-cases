<?php

function absint( $maybeint ) {
	return abs( intval( $maybeint ) );
}

function untrailingslashit( $string ) {
	return rtrim( $string, '/\\' );
}

function set_url_scheme( $url, $scheme = null ) {
	return $url;
}

function get_home_url( $blog_id = null, $path = '', $scheme = null ) {
	$url = 'http://example.com';

	if ( $path && is_string( $path ) )
		$url .= '/' . ltrim( $path, '/' );

	return $url;
}

function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1) {
	return true;
}

function apply_filters( $filter, $data ) {
	return $data;
}

/**
 * Removes any invalid control characters in $string.
 *
 * Also removes any instance of the '\0' string.
 *
 * @since 1.0.0
 *
 * @param string $string
 * @param array $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
 * @return string
 */
function wp_kses_no_null( $string, $options = null ) {
	if ( ! isset( $options['slash_zero'] ) ) {
		$options = array( 'slash_zero' => 'remove' );
	}

	$string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
	if ( 'remove' == $options['slash_zero'] ) {
		$string = preg_replace( '/\\\\+0+/', '', $string );
	}

	return $string;
}

/**
 * Inline CSS filter
 *
 * @since 2.8.1
 *
 * @param string $css        A string of CSS rules.
 * @param string $deprecated Not used.
 * @return string            Filtered string of CSS rules.
 */
function safecss_filter_attr( $css, $deprecated = '' ) {
	//if ( !empty( $deprecated ) )
		//_deprecated_argument( __FUNCTION__, '2.8.1' ); // Never implemented

	$css = wp_kses_no_null($css);
	$css = str_replace(array("\n","\r","\t"), '', $css);

	if ( preg_match( '%[\\\\(&=}]|/\*%', $css ) ) // remove any inline css containing \ ( & } = or comments
		return '';

	$css_array = explode( ';', trim( $css ) );

	/**
	 * Filters list of allowed CSS attributes.
	 *
	 * @since 2.8.1
	 * @since 4.4.0 Added support for `min-height`, `max-height`, `min-width`, and `max-width`.
	 * @since 4.6.0 Added support for `list-style-type`.
	 *
	 * @param array $attr List of allowed CSS attributes.
	 */
	$allowed_attr = apply_filters( 'safe_style_css', array(
		'background',
		'background-color',

		'border',
		'border-width',
		'border-color',
		'border-style',
		'border-right',
		'border-right-color',
		'border-right-style',
		'border-right-width',
		'border-bottom',
		'border-bottom-color',
		'border-bottom-style',
		'border-bottom-width',
		'border-left',
		'border-left-color',
		'border-left-style',
		'border-left-width',
		'border-top',
		'border-top-color',
		'border-top-style',
		'border-top-width',

		'border-spacing',
		'border-collapse',
		'caption-side',

		'color',
		'font',
		'font-family',
		'font-size',
		'font-style',
		'font-variant',
		'font-weight',
		'letter-spacing',
		'line-height',
		'text-decoration',
		'text-indent',
		'text-align',

		'height',
		'min-height',
		'max-height',

		'width',
		'min-width',
		'max-width',

		'margin',
		'margin-right',
		'margin-bottom',
		'margin-left',
		'margin-top',

		'padding',
		'padding-right',
		'padding-bottom',
		'padding-left',
		'padding-top',

		'clear',
		'cursor',
		'direction',
		'float',
		'overflow',
		'vertical-align',
		'list-style-type',
	) );

	if ( empty($allowed_attr) )
		return $css;

	$css = '';
	foreach ( $css_array as $css_item ) {
		if ( $css_item == '' )
			continue;
		$css_item = trim( $css_item );
		$found = false;
		if ( strpos( $css_item, ':' ) === false ) {
			$found = true;
		} else {
			$parts = explode( ':', $css_item );
			if ( in_array( trim( $parts[0] ), $allowed_attr ) )
				$found = true;
		}
		if ( $found ) {
			if( $css != '' )
				$css .= ';';
			$css .= $css_item;
		}
	}

	return $css;
}