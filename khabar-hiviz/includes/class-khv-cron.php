<?php
/**
 * زمان‌بندی (Cron) چک فیدها و پردازش صف.
 *
 * دو رویداد مجزا:
 * - khv_scan_feeds:    چک فیدها و پرکردن صف (با بازه انتخابی کاربر)
 * - khv_process_queue: پردازش صف (هر ۱ دقیقه، تعداد محدود) تا هاست فشار نیاید
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KHV_Cron {

	const SCAN_EVENT    = 'khv_scan_feeds';
	const PROCESS_EVENT = 'khv_process_queue';
	const SCAN_LOCK     = 'khv_scan_lock';
	const PROCESS_LOCK  = 'khv_process_lock';
	const SCAN_TTL      = 300;  // ۵ دقیقه
	const PROCESS_TTL   = 600;  // ۱۰ دقیقه

	/**
	 * ثبت فیلترها و هوک‌ها.
	 */
	public static function register() {
		add_filter( 'cron_schedules', array( self::class, 'schedules' ) );
		add_action( self::SCAN_EVENT, array( self::class, 'run_scan' ) );
		add_action( self::PROCESS_EVENT, array( self::class, 'run_process' ) );
	}

	/**
	 * افزودن بازه‌های سفارشی.
	 *
	 * @param array $schedules بازه‌های فعلی.
	 * @return array
	 */
	public static function schedules( $schedules ) {
		$schedules['khv_1min']  = array(
			'interval' => 60,
			'display'  => 'هر ۱ دقیقه (خبریاب)',
		);
		$schedules['khv_5min']  = array(
			'interval' => 5 * MINUTE_IN_SECONDS,
			'display'  => 'هر ۵ دقیقه (خبریاب)',
		);
		$schedules['khv_15min'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => 'هر ۱۵ دقیقه (خبریاب)',
		);
		$schedules['khv_30min'] = array(
			'interval' => 30 * MINUTE_IN_SECONDS,
			'display'  => 'هر ۳۰ دقیقه (خبریاب)',
		);
		return $schedules;
	}

	/**
	 * تنظیم مجدد زمان‌بندی بر اساس تنظیمات.
	 */
	public static function reschedule() {
		$settings = KHV_Settings::get();
		$manual   = 'manual' === $settings['interval'];

		wp_clear_scheduled_hook( self::SCAN_EVENT );
		wp_clear_scheduled_hook( self::PROCESS_EVENT );

		if ( $manual ) {
			return;
		}

		wp_schedule_event( time() + 60, $settings['interval'], self::SCAN_EVENT );
		wp_schedule_event( time() + 90, 'khv_1min', self::PROCESS_EVENT );
	}

	/**
	 * حذف زمان‌بندی.
	 */
	public static function clear() {
		wp_clear_scheduled_hook( self::SCAN_EVENT );
		wp_clear_scheduled_hook( self::PROCESS_EVENT );
	}

	/**
	 * اسکن فیدها از cron.
	 *
	 * توجه: حتی در حالت توقف (paused) هم اسکن ادامه می‌یابد تا صف پر بماند؛
	 * فقط پردازش (ساخت پیش‌نویس) متوقف است.
	 */
	public static function run_scan() {
		if ( ! self::acquire_lock( self::SCAN_LOCK, self::SCAN_TTL ) ) {
			return;
		}
		@set_time_limit( 0 ); // phpcs:ignore
		KHV_Processor::scan_feeds();
		self::release_lock( self::SCAN_LOCK );
	}

	/**
	 * پردازش صف از cron.
	 */
	public static function run_process() {
		if ( KHV_Processor::is_paused() ) {
			return;
		}
		if ( ! self::acquire_lock( self::PROCESS_LOCK, self::PROCESS_TTL ) ) {
			return;
		}
		@set_time_limit( 0 ); // phpcs:ignore
		KHV_Processor::process_queue();
		self::release_lock( self::PROCESS_LOCK );
	}

	/**
	 * گرفتن قفل (جلوگیری از اجرای هم‌زمان).
	 *
	 * @param string $option نام option قفل.
	 * @param int    $ttl    مدت اعتبار (ثانیه).
	 * @return bool
	 */
	public static function acquire_lock( $option, $ttl ) {
		$lock = (int) get_option( $option, 0 );
		if ( $lock && ( time() - $lock ) < $ttl ) {
			return false;
		}
		update_option( $option, time(), false );
		return true;
	}

	/**
	 * رها کردن قفل.
	 *
	 * @param string $option نام option قفل.
	 */
	public static function release_lock( $option ) {
		delete_option( $option );
	}

	/**
	 * آیا همین الان اجرا (اسکن یا پردازش) در حال انجام است؟
	 *
	 * @return bool
	 */
	public static function is_running() {
		return self::lock_active( self::SCAN_LOCK, self::SCAN_TTL ) || self::lock_active( self::PROCESS_LOCK, self::PROCESS_TTL );
	}

	/**
	 * آیا یک قفل فعال است؟
	 *
	 * @param string $option نام option قفل.
	 * @param int    $ttl    مدت اعتبار.
	 * @return bool
	 */
	private static function lock_active( $option, $ttl ) {
		$lock = (int) get_option( $option, 0 );
		return $lock && ( time() - $lock ) < $ttl;
	}
}
