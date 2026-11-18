<?php
/**
 * لاگ داخلی پلاگین خبریاب.
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KHV_Log {

	const OPTION = 'khv_log';

	/**
	 * یک رکورد در لاگ ثبت می‌کند.
	 *
	 * @param string $msg   متن.
	 * @param string $level info|warn|error.
	 */
	public static function add( $msg, $level = 'info' ) {
		$log   = get_option( self::OPTION, array() );
		$log[] = array(
			't'   => time(),
			'lvl' => in_array( $level, array( 'info', 'warn', 'error' ), true ) ? $level : 'info',
			'msg' => mb_substr( (string) $msg, 0, 500 ),
		);
		if ( count( $log ) > 200 ) {
			$log = array_slice( $log, -200 );
		}
		update_option( self::OPTION, $log, false );
	}

	/**
	 * همه رکوردها (قدیمی به جدید).
	 *
	 * @return array
	 */
	public static function all() {
		$log = get_option( self::OPTION, array() );
		return is_array( $log ) ? $log : array();
	}

	/**
	 * پاک‌کردن لاگ.
	 */
	public static function clear() {
		delete_option( self::OPTION );
	}
}
