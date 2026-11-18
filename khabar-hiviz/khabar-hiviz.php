<?php
/**
 * Plugin Name:       خبریاب هوشمند
 * Plugin URI:        #
 * Description:       آخرین اخبار را از فیدهای RSS دسته‌بندی‌ها می‌گیرد، با هوش مصنوعی تیتر و متن را بازنویسی و پاکسازی می‌کند و به‌صورت پیش‌نویس در سایت درج می‌کند.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Khabar Hiviz
 * Text Domain:       khv
 * License:           GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KHV_VERSION', '1.0.0' );
define( 'KHV_FILE', __FILE__ );
define( 'KHV_BASENAME', plugin_basename( __FILE__ ) );
define( 'KHV_PATH', plugin_dir_path( __FILE__ ) );
define( 'KHV_URL', plugin_dir_url( __FILE__ ) );

require_once KHV_PATH . 'includes/class-khv-log.php';
require_once KHV_PATH . 'includes/class-khv-settings.php';
require_once KHV_PATH . 'includes/class-khv-ai.php';
require_once KHV_PATH . 'includes/class-khv-feeds.php';
require_once KHV_PATH . 'includes/class-khv-article.php';
require_once KHV_PATH . 'includes/class-khv-processor.php';
require_once KHV_PATH . 'includes/class-khv-cron.php';
require_once KHV_PATH . 'admin/class-khv-admin.php';

/**
 * کارهای فعال‌سازی پلاگین.
 *
 * @package KhabarHiviz
 */
class KHV_Activator {

	/**
	 * هنگام فعال‌شدن افزونه: تنظیم cron + ساخت خودکار دسته‌های مقصد.
	 */
	public static function activate() {
		KHV_Cron::reschedule();

		// افزودن فیدهای پیش‌فرض داخلی (فقط آن‌هایی که در تنظیمات نیستند)
		$seeded = KHV_Settings::seed_default_feeds();
		if ( $seeded > 0 ) {
			KHV_Log::add( $seeded . ' فید پیش‌فرض به تنظیمات اضافه شد.', 'info' );
		}

		$res = KHV_Settings::ensure_categories();
		if ( ! empty( $res['created'] ) ) {
			KHV_Log::add( 'دسته‌های تازه‌ساخته هنگام فعال‌سازی: ' . implode( '، ', $res['created'] ), 'info' );
		}
		if ( ! empty( $res['found'] ) ) {
			KHV_Log::add( 'دسته‌های موجود استفاده‌شده هنگام فعال‌سازی: ' . implode( '، ', $res['found'] ), 'info' );
		}
		if ( ! empty( $res['errors'] ) ) {
			KHV_Log::add( 'خطا در ساخت دسته هنگام فعال‌سازی: ' . implode( '؛ ', $res['errors'] ), 'error' );
		}
	}
}

register_activation_hook( __FILE__, array( 'KHV_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'KHV_Cron', 'clear' ) );

add_action( 'init', array( 'KHV_Cron', 'register' ) );

add_action( 'admin_menu', array( 'KHV_Admin', 'menu' ) );
add_action( 'admin_enqueue_scripts', array( 'KHV_Admin', 'assets' ) );
add_action( 'admin_post_khv_save_settings', array( 'KHV_Admin', 'save_settings' ) );
add_action( 'admin_post_khv_clear_log', array( 'KHV_Admin', 'clear_log' ) );
add_action( 'admin_post_khv_start_from_now', array( 'KHV_Admin', 'start_from_now' ) );
add_action( 'admin_post_khv_toggle_pause', array( 'KHV_Admin', 'toggle_pause' ) );
add_action( 'wp_ajax_khv_test_ai', array( 'KHV_Admin', 'ajax_test_ai' ) );
add_action( 'wp_ajax_khv_test_feed', array( 'KHV_Admin', 'ajax_test_feed' ) );
add_action( 'wp_ajax_khv_run_now', array( 'KHV_Admin', 'ajax_run_now' ) );
add_action( 'wp_ajax_khv_ensure_cats', array( 'KHV_Admin', 'ajax_ensure_cats' ) );
