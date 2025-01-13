<?php
namespace MetaBox\UserProfile;

use WP_Post;

class Helper {
	public static function convert_boolean( $value ): string {
		return $value ? 'true' : 'false';
	}
}
