<?php
/**
 * بخش مدیریت پلاگین خبریاب.
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KHV_Admin {

	/**
	 * ثبت منوی مدیریت.
	 */
	public static function menu() {
		add_menu_page(
			'خبریاب هوشمند',
			'خبریاب هوشمند',
			'manage_options',
			'khabar-hiviz',
			array( self::class, 'page' ),
			'dashicons-megaphone',
			26
		);
	}

	/**
	 * لود CSS/JS صفحه.
	 *
	 * @param string $hook صفحه فعلی.
	 */
	public static function assets( $hook ) {
		if ( 'toplevel_page_khabar-hiviz' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'khv-admin', KHV_URL . 'admin/admin.css', array(), KHV_VERSION );
		wp_enqueue_script( 'khv-admin', KHV_URL . 'admin/admin.js', array( 'jquery' ), KHV_VERSION, true );

		$cat_map = array();
		$terms   = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$cat_map[ (int) $t->term_id ] = $t->name;
			}
		}

		wp_localize_script(
			'khv-admin',
			'khvData',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'khv_admin' ),
				'cats'       => $cat_map,
				'catDefault' => 'دسته پیش‌فرض',
			)
		);
	}

	/**
	 * رندر صفحه تنظیمات.
	 */
	public static function page() {
		$settings  = KHV_Settings::get();
		$models    = KHV_AI::list_models( $settings );
		if ( '' !== $settings['ai_model'] && ! in_array( $settings['ai_model'], $models, true ) ) {
			array_unshift( $models, $settings['ai_model'] );
		}

		// کلید واقعی هرگز به HTML/براکر نمی‌رود؛ فقط وضعیت و ماسک نمایش داده می‌شود.
		$settings['api_key_set']  = '' !== $settings['api_key'];
		$settings['api_key_mask'] = $settings['api_key_set'] ? '••••••••' : '';
		$settings['api_key']      = '';

		// فهرست دسته‌ها یک‌بار خوانده می‌شود (برای ۱۷۰+ ردیف فید: یک query به‌جای صد‌ها)
		$cat_terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
		if ( is_wp_error( $cat_terms ) ) {
			$cat_terms = array();
		}

		$post_types = KHV_Settings::post_type_choices();
		$logs       = array_reverse( KHV_Log::all() );
		$notice     = get_transient( 'khv_notice' );
		if ( $notice ) {
			delete_transient( 'khv_notice' );
		}
		$saved_flag = isset( $_GET['khv_saved'] ) ? 1 : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		include KHV_PATH . 'admin/views/settings.php';
	}

	/**
	 * ذخیره تنظیمات (admin-post).
	 */
	public static function save_settings() {
		check_admin_referer( 'khv_save', 'khv_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'دسترسی ندارید' );
		}

		$result = KHV_Settings::save( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( is_wp_error( $result ) ) {
			set_transient( 'khv_notice', $result->get_error_message(), 120 );
		} else {
			set_transient( 'khv_notice', 'تنظیمات ذخیره شد.', 120 );
		}

		wp_safe_redirect( add_query_arg( 'khv_saved', '1', admin_url( 'admin.php?page=khabar-hiviz' ) ) );
		exit;
	}

	/**
	 * پاک‌کردن لاگ (admin-post).
	 */
	public static function clear_log() {
		check_admin_referer( 'khv_clear_log', '_wpnonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'دسترسی ندارید' );
		}
		KHV_Log::clear();
		set_transient( 'khv_notice', 'لاگ پاک شد.', 120 );
		wp_safe_redirect( admin_url( 'admin.php?page=khabar-hiviz' ) );
		exit;
	}

	/**
	 * تست اتصال به API هوش مصنوعی (ajax).
	 */
	public static function ajax_test_ai() {
		check_ajax_referer( 'khv_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'دسترسی ندارید', 403 );
		}

		$settings = KHV_Settings::get();
		$r        = KHV_AI::chat(
			$settings,
			array( array( 'role' => 'user', 'content' => 'فقط کلمه «موفق» را خروجی بده.' ) ),
			4096,
			0.2
		);

		if ( $r['ok'] ) {
			wp_send_json_success( 'اتصال برقرار شد — پاسخ مدل: ' . mb_substr( $r['content'], 0, 150 ) );
		}
		wp_send_json_error( 'اتصال ناموفق: ' . $r['error'] );
	}

	/**
	 * اجرای فوری پردازش (ajax): اسکن فیدها + پردازش صف.
	 */
	public static function ajax_run_now() {
		check_ajax_referer( 'khv_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'دسترسی ندارید', 403 );
		}

		if ( ! KHV_Cron::acquire_lock( KHV_Cron::SCAN_LOCK, KHV_Cron::SCAN_TTL ) || ! KHV_Cron::acquire_lock( KHV_Cron::PROCESS_LOCK, KHV_Cron::PROCESS_TTL ) ) {
			KHV_Cron::release_lock( KHV_Cron::SCAN_LOCK );
			KHV_Cron::release_lock( KHV_Cron::PROCESS_LOCK );
			wp_send_json_error( 'یک اجرا همین حالا در حال انجام است؛ کمی صبر کنید.' );
		}

		@set_time_limit( 0 ); // phpcs:ignore

		$summary = KHV_Processor::run_now();

		KHV_Cron::release_lock( KHV_Cron::SCAN_LOCK );
		KHV_Cron::release_lock( KHV_Cron::PROCESS_LOCK );

		$left = KHV_Processor::queue_count();
		wp_send_json_success( sprintf( 'اجرا کامل شد: %d پیش‌نویس از %d فید (خطا: %d). در صف: %d خبر.', $summary['created'], $summary['feeds'], $summary['errors'], $left ) );
	}

	/**
	 * ساخت خودکار دسته‌های کم‌تعداد برای فیدها (ajax).
	 */
	public static function ajax_ensure_cats() {
		check_ajax_referer( 'khv_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'دسترسی ندارید', 403 );
		}

		$res = KHV_Settings::ensure_categories();

		if ( ! empty( $res['errors'] ) ) {
			wp_send_json_error( 'خطا در ساخت دسته‌ها: ' . implode( '؛ ', $res['errors'] ) );
		}

		$parts = array();
		if ( ! empty( $res['created'] ) ) {
			$parts[] = count( $res['created'] ) . ' دسته جدید ساخته شد (' . implode( '، ', $res['created'] ) . ')';
		}
		if ( ! empty( $res['found'] ) ) {
			$parts[] = count( $res['found'] ) . ' فید به دسته موجود متصل شد';
		}
		if ( $res['skipped'] ) {
			$parts[] = $res['skipped'] . ' فید رد شد (قبلاً دسته دارد)';
		}

		wp_send_json_success( ! empty( $parts ) ? implode( ' — ', $parts ) : 'چیزی انجام نشد؛ همه فیدها دسته مقصد دارند.' );
	}

	/**
	 * شروع از این لحظه: ثبت نقطه شروع + پاک‌کردن صف (admin-post).
	 */
	public static function start_from_now() {
		check_admin_referer( 'khv_start_from_now' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'دسترسی ندارید' );
		}

		$discarded = KHV_Processor::queue_count();
		KHV_Processor::set_cutoff( time() );
		KHV_Processor::clear_queue();

		KHV_Log::add( 'نقطه شروع دریافت اخبار ثبت شد؛ ' . $discarded . ' خبر از صف کنار گذاشته شد.', 'info' );
		set_transient( 'khv_notice', 'نقطه شروع جدید ثبت شد؛ فقط خبرهای منتشرشده پس از این لحظه پردازش می‌شوند.', 120 );
		wp_safe_redirect( admin_url( 'admin.php?page=khabar-hiviz' ) );
		exit;
	}

	/**
	 * توقف / ادامه پردازش (admin-post).
	 */
	public static function toggle_pause() {
		check_admin_referer( 'khv_toggle_pause' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'دسترسی ندارید' );
		}

		$paused = ! KHV_Processor::is_paused();
		KHV_Processor::set_paused( $paused );

		$msg = $paused ? 'پردازش متوقف شد؛ صف و تنظیمات حفظ شدند.' : 'پردازش ادامه پیدا می‌کند.';
		KHV_Log::add( $msg, 'info' );
		set_transient( 'khv_notice', $msg, 120 );
		wp_safe_redirect( admin_url( 'admin.php?page=khabar-hiviz' ) );
		exit;
	}

	/**
	 * تست اتصال یک فید RSS (ajax): آخرین خبر فید را برمی‌گرداند.
	 */
	public static function ajax_test_feed() {
		check_ajax_referer( 'khv_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'دسترسی ندارید', 403 );
		}

		$url = isset( $_POST['url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['url'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $url === '' || ! preg_match( '#^https?://#i', $url ) ) {
			wp_send_json_error( 'آدرس فید معتبر نیست.' );
		}

		$res = KHV_Feeds::fetch( $url );
		if ( isset( $res['error'] ) ) {
			wp_send_json_error( 'دریافت فید ناموفق بود: ' . $res['error'] );
		}
		if ( empty( $res['items'] ) ) {
			wp_send_json_error( 'آیتمی در فید پیدا نشد.' );
		}

		$item = $res['items'][0];
		$desc = trim( wp_strip_all_tags( $item['description'] ) );
		wp_send_json_success( 'آخرین خبر: ' . $item['title'] . ' | تاریخ: ' . ( $item['pubDate'] !== '' ? $item['pubDate'] : 'نامشخص' ) . ' | خلاصه: ' . wp_trim_words( $desc, 20 ) );
	}
}
