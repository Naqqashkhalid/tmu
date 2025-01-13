<?php
/**
 * Store form configs in a persistent option that can be retrieved on form requests.
 */

namespace MetaBox\UserProfile;

class ConfigStorage {
	const OPTION_NAME = 'mbup_keys';

	public static function get( string $key ) : array {
		$option = get_option( self::OPTION_NAME );
		return $option[ $key ] ?? [];
	}

	public static function store( array $config ) : string {
		$option         = get_option( self::OPTION_NAME );
		$key            = self::get_key( $config );
		$option[ $key ] = $config;
		update_option( self::OPTION_NAME, $option );

		return $key;
	}

	public static function get_key( array $config ) : string {
		return md5( serialize( $config ) );
	}
}
