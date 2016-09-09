<?php

//require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

/**
 * Collects inline styles and outputs them in the amp-custom stylesheet.
 */
class AMP_Style_Sanitizer extends AMP_Base_Sanitizer {
	private $styles = array();

	public function sanitize() {
		$body = $this->get_body_node();
		$this->collect_styles_recursive( $body );

		if ( ! empty( $this->styles ) ) {
			add_action( 'amp_post_template_css', array( $this, 'append_styles' ), 99 );
		}
	}

	private function collect_styles_recursive( $node ) {
		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}

		if ( $node->hasAttributes() && $node instanceof DOMElement ) {
			$style = $node->getAttribute( 'style' );
			$class = $node->getAttribute( 'class' );

			if ( $style ) {
				$style = $this->process_style( $style );

				$class_name = $this->generate_class_name( $style );
				$new_class  = trim( $class . ' ' . $class_name );

				$node->setAttribute( 'class', $new_class );
				$this->styles[ $class_name ] = $style;
			}
		}

		$length = $node->childNodes->length;
		for ( $i = $length - 1; $i >= 0; $i -- ) {
			$child_node = $node->childNodes->item( $i );

			$this->collect_styles_recursive( $child_node );
		}
	}

	private function process_style( $string ) {
		// Filter properties
		$string = safecss_filter_attr( $string );

		// Normalize order
		$arr = array_map( 'trim', explode( ';', $string ) );
		sort( $arr );

		// Handle overflow rule
		// https://www.ampproject.org/docs/reference/spec.html#properties
		if ( $overflow = preg_grep( '/^overflow.*(auto|scroll)$/', $arr ) ) {
			foreach( $overflow as $index => $rule ) {
				unset( $arr[ $index ] );
			}
		}

		return implode( ";\n\t", $arr ) . ';';
	}

	private function generate_class_name( $string ) {
		return 'amp-inline-style-' . md5( $string );
	}

	public function append_styles() {
		?>

/* Inline Styles */
<?php foreach ( $this->styles as $class_name => $style ) : ?>
.<?php echo $class_name; ?> {
	<?php echo $style; ?>

}
<?php endforeach; ?>

		<?php
	}
}