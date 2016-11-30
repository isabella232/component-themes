<?php
require( dirname( __DIR__ ) . '/vendor/CSS-Parser/parser.php' );

class Component_Themes_Styles {
	public function build_styles_from_theme( $theme_config ) {
		$styles = isset( $theme_config['styles'] ) ? $theme_config['styles'] : [];
		if ( is_string( $styles ) ) {
			return $this->prepend_namespace_to_style_string( '.ComponentThemes', $this->expand_style_variants( $styles, $theme_config ) );
		}
		return $this->expand_style_variants( implode( '', array_map( function( $key ) use ( &$styles ) {
			return $this->build_style_block( $key, $styles[ $key ] );
		}, array_keys( $styles ) ) ), $theme_config );
	}

	private function expand_style_variants( $styles, $theme_config ) {
		if ( ! isset( $theme_config['variant-styles'] ) || ! isset( $theme_config['active-variant-styles'] ) ) {
			return $styles;
		}
		$variants = $theme_config['variant-styles'];
		$active_variants = isset( $theme_config['active-variant-styles'] ) ? $theme_config['active-variant-styles'] : [];
		$defaults = isset( $variants['defaults'] ) ? $variants['defaults'] : [];
		$final_variants = array_reduce( $active_variants, function( $prev, $variant_key ) use ( &$variants ) {
			$variant = isset( $variants[ $variant_key ] ) ? $variants[ $variant_key ] : [];
			return array_merge( $prev, $variant );
		}, $defaults );
		return array_reduce( array_keys( $final_variants ), function( $prev, $var_name ) use ( &$final_variants ) {
			return str_replace( '$' . $var_name, $final_variants[ $var_name ], $prev );
		}, $styles );
	}

	private function build_style_block( $key, $style ) {
		return ".ComponentThemes $key{" . $this->get_style_string_from_style_data( $style ) . '}';
	}

	private function get_style_string_from_style_data( $style ) {
		return is_array( $style ) ? implode( '', $style ) : $style;
	}

	private function prepend_namespace_to_node( $namespace, $val ) {
		$new_val = $val;
		if ( is_array( $val ) ) {
			$new_val = [];
			foreach ( $val as $child_key => $child_val ) {
				$new_key = $child_key;
				if ( '.' === $child_key[0] || '#' === $child_key[0] ) {
					$new_key = $namespace . ' ' . $child_key;
				}
				$new_val[ $new_key ] = $this->prepend_namespace_to_node( $namespace, $child_val );
			}
		}
		return $new_val;
	}

	public function prepend_namespace_to_style_string( $namespace, $styles ) {
		$parser = new \CssParser();
		$parser->load_string( $styles );
		$parser->parse();
		$parser->parsed = $this->prepend_namespace_to_node( $namespace, $parser->parsed );
		return $parser->glue();
	}
}