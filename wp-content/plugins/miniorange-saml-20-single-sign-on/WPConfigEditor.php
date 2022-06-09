<?php


class WPConfigEditor
{
	/**
	 * Path to wp-config.php file
	 * @var string
	 */
	private $wp_config_path;

	/**
	 * wp-config.php file contents
	 * @var mixed
	 */
	private $wp_config_src;

	/**
	 * SAML Utilities object to handle error messages
	 * @var Utilities
	 */
	private $mo_saml_utils;

	public function __construct( $wp_config_path ) {
		$basename = basename( $wp_config_path );
		$this->wp_config_path = $wp_config_path;
		$this->mo_saml_utils = new Utilities();
		if ( ! file_exists( $wp_config_path ) ) {
			update_option( 'mo_saml_message', $basename . __('File doesn\'t exist.','miniorange-saml-20-single-sign-on' ));
			$this->mo_saml_utils->mo_saml_show_error_message();
		}
		$wp_config_src = file_get_contents( $this->wp_config_path );
		$this->wp_config_src = str_replace( array( "\n\r", "\r" ), "\n", $wp_config_src );

	}

	/**
	 * Save the contents in the wp-config file
	 *
	 * @param $contents
	 * @return bool
	 */
	function save( $contents,$value ) {
		if ( ! trim( $contents ) || $contents === $this->wp_config_src) {
			update_option( 'mo_saml_message', __('Failed to update the WP config file. Please enable the debug-logs manually','miniorange-saml-20-single-sign-on' ));
			$this->mo_saml_utils->mo_saml_show_error_message();
			return false;
		}
		$result = file_put_contents( $this->wp_config_path, $contents, LOCK_EX );
		if ( false === $result ) {
			update_option( 'mo_saml_message', __('Failed to update the WP config file. Please enable the debug-logs manually','miniorange-saml-20-single-sign-on' ));
			$this->mo_saml_utils->mo_saml_show_error_message();
		}
		update_option( 'mo_saml_message',  __('Configuration Saved Successfully','miniorange-saml-20-single-sign-on' ));
		$this->mo_saml_utils->mo_saml_show_success_message();
		return true;
	}

	/**
	 * Adds a config to the wp-config.php file.
	 *
	 * @param string $name    Config name.
	 * @param string $value   Config value.
	 *
	 * @return bool
	 */
	public function add( $name, $value ) {
		if ( ! is_string( $value ) ) {
			update_option( 'mo_saml_message', __('Config value must be a string.','miniorange-saml-20-single-sign-on' ));
			$this->mo_saml_utils->mo_saml_show_error_message();
			return false;
		}

		if ( $this->exists( $name ) ) {
			return false;
		}

		$anchor    = "/* That's all, stop editing!";
		$separator = PHP_EOL;

		if ( 'EOF' === $anchor ) {
			$contents = $this->wp_config_src . $this->normalize( $name,  $value );
		} else {
			if ( false === strpos( $this->wp_config_src, $anchor ) ) {
				update_option( 'mo_saml_message', __('Unable to locate placement anchor.','miniorange-saml-20-single-sign-on' ));
				$this->mo_saml_utils->mo_saml_show_error_message();
				return false;

			}

			$new_src  = $this->normalize( $name, $value );
			$new_src  = $new_src . $separator . $anchor;
			$contents = str_replace( $anchor, $new_src, $this->wp_config_src );
		}

		return $this->save( $contents,$value );
	}

	/**
	 * Updates an existing config in the wp-config.php file.
	 *
	 * @param string $name Config name.
	 * @param string $value Config value.
	 *
	 * @return bool
	 */
	public function update( $name, $value ) {
		if ( ! is_string( $value ) ) {
			update_option( 'mo_saml_message', __('Config value must be a string.','miniorange-saml-20-single-sign-on' ));
			$this->mo_saml_utils->mo_saml_show_error_message();
			return false;
		}

		if ( ! $this->exists( $name ) ) {
			return $this->add( $name, $value );
		}

		$old_src   = $this->wp_configs[ $name ]['src'];
		$old_value = $this->wp_configs[ $name ]['value'];
		$new_value = $value;


		$new_parts    = $this->wp_configs[ $name ]['parts'];
		$new_parts[1] = str_replace( $old_value, $new_value, $new_parts[1] );
		$new_src      = implode( '', $new_parts );

		$contents = preg_replace(
			sprintf( '/(?<=^|;|<\?php\s|<\?\s)(\s*?)%s/m', preg_quote( trim( $old_src ), '/' ) ),
			'$1' . str_replace( '$', '\$', trim( $new_src ) ),
			$this->wp_config_src
		);

		return $this->save( $contents,$value );
	}

	/**
	 * Normalizes the source output for a name/value pair.
	 *
	 * @param string $name  Config name.
	 * @param mixed  $value Config value.
	 *
	 * @return string
	 */
	protected function normalize( $name, $value ) {
		$placeholder = "define( '%s', %s );";
		return sprintf( $placeholder, $name, $value );
	}

	/**
	 * Parses the source of a wp-config.php file.
	 *
	 * @param string $src Config file source.
	 *
	 * @return array
	 */
	protected function parse_wp_config( $src ) {
		$configs             = array();

		// Strip comments.
		foreach ( token_get_all( $src ) as $token ) {
			if ( in_array( $token[0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
				$src = str_replace( $token[1], '', $src );
			}
		}

		preg_match_all( '/(?<=^|;|<\?php\s|<\?\s)(\h*define\s*\(\s*[\'"](\w*?)[\'"]\s*)(,\s*(\'\'|""|\'.*?[^\\\\]\'|".*?[^\\\\]"|.*?)\s*)((?:,\s*(?:true|false)\s*)?\)\s*;)/ims', $src, $constants );

		if ( ! empty( $constants[0] ) && ! empty( $constants[1] ) && ! empty( $constants[2] ) && ! empty( $constants[3] ) && ! empty( $constants[4] ) && ! empty( $constants[5] ) ) {
			foreach ( $constants[2] as $index => $name ) {
				$configs[ $name ] = array(
					'src'   => $constants[0][ $index ],
					'value' => $constants[4][ $index ],
					'parts' => array(
						$constants[1][ $index ],
						$constants[3][ $index ],
						$constants[5][ $index ],
					),
				);
			}
		}

		return $configs;
	}

	/**
	 * Get the value of a config in the wp-config.php file.
	 *
	 * @param string $name Config name.
	 *
	 * @return array
	 */
	public function get_value( $name ) {
		$wp_config_src = file_get_contents($this->wp_config_path );

		if ( ! trim( $wp_config_src ) ) {
			update_option( 'mo_saml_message', __('<code>Wp-config.php</code> file is empty.','miniorange-saml-20-single-sign-on' ));
			$this->mo_saml_utils->mo_saml_show_error_message();
			return false;

		}

		$this->wp_config_src = $wp_config_src;
		$this->wp_configs    = $this->parse_wp_config( $this->wp_config_src );

		if ( ! isset( $this->wp_configs[ $name ] ) ) {
			return false;
		}

		return $this->wp_configs[ $name ]['value'];
	}

	/**
	 * Checks if a config exists in the wp-config.php file.
	 *
	 * @param string $name Config name.
	 *
	 * @return bool
	 */
	public function exists( $name ) {
		$wp_config_src = file_get_contents( $this->wp_config_path );

		if ( ! trim( $wp_config_src ) ) {
			update_option( 'mo_saml_message', __('<code>Wp-config.php</code> file is empty.','miniorange-saml-20-single-sign-on' ));
			$this->mo_saml_utils->mo_saml_show_error_message();
			return false;
		}
		// Normalize the newline to prevent an issue coming from OSX.
		$this->wp_config_src = str_replace( array( "\n\r", "\r" ), "\n", $wp_config_src );
		$this->wp_configs    = $this->parse_wp_config( $this->wp_config_src );

		if ( ! isset( $this->wp_configs[ $name ] ) ) {
			return false;
		}
		return isset( $this->wp_configs[ $name ] );
	}

}