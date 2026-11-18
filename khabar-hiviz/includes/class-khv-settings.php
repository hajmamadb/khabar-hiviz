<?php
/**
 * تنظیمات پلاگین خبریاب.
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KHV_Settings {

	const OPTION = 'khv_settings';

	/**
	 * مقادیر پیش‌فرض.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			// --- هوش مصنوعی ---
			'api_url'    => 'https://ai.ismc.ir/api/v1',
			'api_key'    => 'sk-5K6TXne1qfnoiUWXvbQ0lg',
			'ai_model'   => 'qwen38-sglang',

			// --- فیدها (هر مورد: name, url, category, enabled, max_items) ---
			// فهرست کامل فیدهای پیش‌فرض در includes/default-feeds.php است و هنگام
			// فعال‌سازی (KHV_Activator) با seed_default_feeds() به تنظیمات اضافه می‌شود.
			'feeds'      => array(),

			// حداکثر تعداد خبر پردازش‌شده در هر تیک پردازش صف (هر ۱ دقیقه)
			'max_items_per_run' => 3,

			// --- درج پیش‌نویس ---
			'post_type'   => 'post',
			'post_status' => 'draft',

			// --- تصویر ---
			// remote  = نمایش مستقیم از CDN مبدأ (بدون دانلود)
			// download= دانلود و ثبت در کتابخانه رسانه
			'image_mode'  => 'remote',

			// --- کلیدواژه‌ها ---
			// tags    = برچسب‌های وردپرس
			// yoast   = کلمه کلیدی هدف Yoast
			// rankmath= کلمه کلیدی هدف RankMath
			// meta    = فیلد اختصاصی
			'keywords_mode' => 'tags',

			// --- ذکر منبع ---
			// none = بدون ذکر منبع
			// line = یک خط «منبع: …» با لینک در انتهای متن
			'attribution'   => 'none',

			// --- بازه چک فید (cron) ---
			// khv_5min | khv_15min | khv_30min | hourly | daily | manual
			'interval'      => 'khv_5min',

			// --- تعداد گروه‌های اسکن ---
			// هر تیک اسکن، فقط یک گروه از فیدها چک می‌شود (چرخشی).
			// ۱ = همه فیدها در هر چک؛ با ۳ گروه و چک هر ۵ دقیقه، کل فیدها هر ۱۵ دقیقه چک می‌شوند.
			'scan_batches'  => 3,

			// --- متن‌های دسته‌ای که باید رد شوند (جداشونده با ویرگول، در category فید) ---
			'skip_cats'     => 'ویدیو',

			// --- حداکثر تعداد کاراکتر متن ارسالی به هوش مصنوعی ---
			'max_text_chars' => 6000,
		);
	}

	/**
	 * خواندن تنظیمات (به‌همراه پیش‌فرض‌ها).
	 *
	 * کلید API در این خروجی به‌صورت <em>رمزگشایی‌شده</em> هست تا مصرف‌کننده‌ها
	 * (AI، پردازنده و ...) بدون تغییر کار کنند؛ در دیتابیس همیشه رمزنگار نگهداری می‌شود.
	 *
	 * @return array
	 */
	public static function get() {
		$s = get_option( self::OPTION, array() );
		if ( ! is_array( $s ) ) {
			$s = array();
		}
		if ( isset( $s['api_key'] ) ) {
			$s['api_key'] = self::decrypt_key( $s['api_key'] );
		}
		return wp_parse_args( $s, self::defaults() );
	}

	/**
	 * ذخیره تنظیمات آماده (بدون عبور از اعتبارسنجی فرم).
	 *
	 * کلید API قبل از ثبت در دیتابیس رمزنگاری می‌شود (AES-256-GCM با کلیدی
	 * مشتق‌شده از wp_salt سایت)، پس در wp_options متن ساده دیده نمی‌شود.
	 *
	 * @param array $settings آرایه کامل تنظیمات.
	 */
	public static function save_array( array $settings ) {
		if ( isset( $settings['api_key'] ) ) {
			$settings['api_key'] = self::encrypt_key( (string) $settings['api_key'] );
		}
		update_option( self::OPTION, $settings );
		KHV_Cron::reschedule();
	}

	/**
	 * راز رمزنگاری: کلید ۲۵۶ بیتی مشتق‌شده از wp_salt + آدرس سایت.
	 *
	 * با wp_salt، این راز در هر نصب (و با wp-config جدید) منحصر‌به‌فرد است.
	 *
	 * @return string
	 */
	public static function get_key_secret() {
		$seed = wp_salt( 'auth' ) . '|' . ( defined( 'WP_SITEURL' ) ? WP_SITEURL : '' );
		return hash_hmac( 'sha256', 'khabar-hiviz/v1', $seed );
	}

	/**
	 * رمزنگاری کلید API برای ذخیره در دیتابیس.
	 *
	 * اگر openssl در دسترس نباشد، به‌جای متن ساده، base64 ذخیره می‌شود
	 * (حداقل دیده نمی‌شود و در PHP سالم کار می‌کند).
	 *
	 * @param string $plain کلید به‌صورت متن ساده.
	 * @return string رشته رمزنگاری‌شده (با پیشوند khvenc1:) یا base64 (با پیشوند plain:).
	 */
	public static function encrypt_key( $plain ) {
		$plain = trim( (string) $plain );
		if ( '' === $plain ) {
			return '';
		}
		if ( ! function_exists( 'openssl_encrypt' ) || ! function_exists( 'random_bytes' ) ) {
			return 'plain:' . base64_encode( $plain );
		}
		$nonce = random_bytes( 12 );
		$tag   = '';
		$ct    = openssl_encrypt( $plain, 'aes-256-gcm', self::get_key_secret(), OPENSSL_RAW_DATA, $nonce, $tag, '', 16 );
		if ( false === $ct ) {
			return 'plain:' . base64_encode( $plain );
		}
		return 'khvenc1:' . base64_encode( $nonce . $tag . $ct );
	}

	/**
	 * رمزگشایی کلید ذخیره‌شده.
	 *
	 * - رشته‌های با پیشوند khvenc1: با AES-256-GCM رمزگشایی می‌شوند.
	 * - رشته‌های با پیشوند plain: (حالت فallback بدون openssl) base64_decode می‌شوند.
	 * - سایر مقادیر به‌عنوان متن سادهٔ قدیمی (legacy) برگردانده می‌شوند
	 *   و در اولین save_array بعدی، خودکار رمزنگاری می‌شوند (مهاجرت).
	 *
	 * @param string $stored مقدار ذخیره‌شده در دیتابیس.
	 * @return string کلید متن ساده (یا رشته خالی در صورت نامعتبری).
	 */
	public static function decrypt_key( $stored ) {
		$stored = (string) $stored;
		if ( '' === $stored ) {
			return '';
		}
		if ( 0 === strncmp( $stored, 'khvenc1:', 8 ) ) {
			if ( ! function_exists( 'openssl_decrypt' ) ) {
				return '';
			}
			$raw = base64_decode( substr( $stored, 8 ), true );
			// 12 (nonce) + 16 (tag) + حداقل 1 بایت ciphertext.
			if ( false === $raw || strlen( $raw ) < 29 ) {
				return '';
			}
			$nonce = substr( $raw, 0, 12 );
			$tag   = substr( $raw, 12, 16 );
			$ct    = substr( $raw, 28 );
			$pt    = openssl_decrypt( $ct, 'aes-256-gcm', self::get_key_secret(), OPENSSL_RAW_DATA, $nonce, $tag );
			return ( false === $pt ) ? '' : $pt;
		}
		if ( 0 === strncmp( $stored, 'plain:', 6 ) ) {
			$v = base64_decode( substr( $stored, 6 ), true );
			return ( false === $v ) ? '' : $v;
		}
		return $stored; // متن سادهٔ قدیمی — در بار بعدی رمزنگاری می‌شود.
	}

	/**
	 * استخراج نام دسته از نام فید.
	 * مثلا «اخبار سیاسی (خبرآنلاین)» → «اخبار سیاسی»
	 *
	 * @param string $feed_name نام فید.
	 * @return string
	 */
	public static function feed_category_name( $feed_name ) {
		$name = trim( (string) $feed_name );
		$name = preg_replace( '/\s*[ (（][^()（）]*[)）]\s*$/u', '', $name );
		return trim( $name );
	}

	/**
	 * ساخت یا پیدا کردن دسته مقصد برای فیدها.
	 *
	 * فقط برای فیدهای فعالی که دسته خالی دارند. نام دسته = نام فید (بدون پسوند منبع در پرانتز).
	 * اگر دسته‌ای با همان نام قبلاً وجود داشته باشد، همان استفاده می‌شود؛ وگرنه ساخته می‌گردد.
	 *
	 * @return array { created: array, found: array, errors: array, skipped: int }
	 */
	public static function ensure_categories() {
		$settings = self::get();
		$res      = array(
			'created' => array(),
			'found'   => array(),
			'errors'  => array(),
			'skipped' => 0,
		);
		$cache = array(); // نام => term_id تا بین فیدها تکراری نسازد.

		foreach ( $settings['feeds'] as $i => $feed ) {
			if ( ! empty( $feed['category'] ) || empty( $feed['enabled'] ) ) {
				if ( ! empty( $feed['category'] ) ) {
					$res['skipped']++;
				}
				continue;
			}
			$name = self::feed_category_name( $feed['name'] );
			if ( $name === '' ) {
				$res['skipped']++;
				continue;
			}
			if ( isset( $cache[ $name ] ) ) {
				$settings['feeds'][ $i ]['category'] = $cache[ $name ];
				continue;
			}

			$term = get_term_by( 'name', $name, 'category' );
			if ( $term ) {
				$term_id       = (int) $term->term_id;
				$res['found'][] = $name;
			} else {
				$new = wp_insert_term(
					$name,
					'category',
					array( 'description' => 'ساخته‌شده توسط افزونه خبریاب هوشمند (بر اساس فید «' . $feed['name'] . '»)' )
				);
				if ( is_wp_error( $new ) ) {
					$res['errors'][] = $name . ': ' . $new->get_error_message();
					continue;
				}
				$term_id         = (int) $new['term_id'];
				$res['created'][] = $name;
			}

			$cache[ $name ]                      = $term_id;
			$settings['feeds'][ $i ]['category'] = $term_id;
		}

		self::save_array( $settings );

		return $res;
	}

	/**
	 * افزودن فیدهای پیش‌فرضِ داخلی که هنوز به تنظیمات اضافه نشده‌اند.
	 *
	 * بر اساس آدرس فید تکراری نمی‌شود؛ فیدهایی که کاربر قبلاً حذف کرده
	 * (در تنظیمات وجود ندارند) بازگشت می‌گیرند — این روش در فعال‌سازی مجدد
	 * (بعد از به‌روزرسانی پلاگین) فیدهای تازه‌افزوده را اضافه می‌کند.
	 * هر فید یک topic دارد که به دستهٔ موضوعی (مثل «اخبار سیاسی») نگاشت
	 * می‌شود؛ دسته در صورت عدم‌وجود ساخته می‌گردد و شناسه‌اش مستقیماً به فید داده می‌شود.
	 *
	 * @return int تعداد فیدهای افزوده‌شده.
	 */
	public static function seed_default_feeds() {
		require_once KHV_PATH . 'includes/default-feeds.php';
		$default_feeds = khv_default_feeds();
		if ( ! is_array( $default_feeds ) || empty( $default_feeds ) ) {
			return 0;
		}

		$settings = self::get();
		$existing = array();
		foreach ( (array) $settings['feeds'] as $f ) {
			if ( ! empty( $f['url'] ) ) {
				$existing[ trim( (string) $f['url'] ) ] = true;
			}
		}

		$topic_cache = array(); // نام topic => term_id
		$added       = 0;
		foreach ( $default_feeds as $d ) {
			if ( ! is_array( $d ) || empty( $d['url'] ) ) {
				continue;
			}
			$url = trim( (string) $d['url'] );
			if ( isset( $existing[ $url ] ) ) {
				continue;
			}

			$cat = 0;
			if ( ! empty( $d['topic'] ) ) {
				$topic = (string) $d['topic'];
				if ( ! isset( $topic_cache[ $topic ] ) ) {
					$term = get_term_by( 'name', $topic, 'category' );
					if ( $term ) {
						$topic_cache[ $topic ] = (int) $term->term_id;
					} else {
						$new = wp_insert_term( $topic, 'category', array( 'description' => 'ساخته‌شده توسط افزونه خبریاب هوشمند' ) );
						if ( is_wp_error( $new ) ) {
							$topic_cache[ $topic ] = 0;
						} else {
							$topic_cache[ $topic ] = (int) $new['term_id'];
						}
					}
				}
				$cat = $topic_cache[ $topic ];
			}

			$settings['feeds'][] = array(
				'name'      => ! empty( $d['name'] ) ? $d['name'] : $url,
				'url'       => $url,
				'category'  => $cat,
				'enabled'   => 1,
				'max_items' => isset( $d['max_items'] ) ? max( 1, min( 50, (int) $d['max_items'] ) ) : 3,
			);
			$existing[ $url ] = true;
			$added++;
		}

		if ( $added > 0 ) {
			self::save_array( $settings );
		}
		return $added;
	}

	/**
	 * انتخاب‌های مجاز نوع پست (عمومی).
	 *
	 * @return array
	 */
	public static function post_type_choices() {
		$types = get_post_types( array( 'public' => true ), 'names' );
		$types['post'] = 'post';
		return array_keys( $types );
	}

	/**
	 * ذخیره تنظیمات از فرم.
	 *
	 * @param array $post داده‌های فرم (wp_unslash شده).
	 * @return array|WP_Error تنظیمات ذخیره‌شده.
	 */
	public static function save( $post ) {
		$d = self::defaults();

		$api_url = isset( $post['api_url'] ) ? esc_url_raw( trim( $post['api_url'] ) ) : $d['api_url'];
		if ( ! preg_match( '#^https?://#i', $api_url ) ) {
			return new WP_Error( 'khv_bad_api_url', 'آدرس API هوش مصنوعی نامعتبر است (باید با http یا https شروع شود).' );
		}

		// اگر فیلد کلید خالی باشد، کلید فعلی حفظ می‌شود؛
		// این‌جوری کلید واقعی هرگز از فرم به سرور ارسال نمی‌شود.
		$submitted_key = isset( $post['api_key'] ) ? sanitize_text_field( $post['api_key'] ) : '';
		if ( '' === $submitted_key ) {
			$api_key = self::get()['api_key'];
		} else {
			$api_key = $submitted_key;
		}
		$ai_model = isset( $post['ai_model'] ) ? sanitize_text_field( $post['ai_model'] ) : $d['ai_model'];

		$feeds = array();
		if ( isset( $post['khv_feeds'] ) && is_array( $post['khv_feeds'] ) ) {
			$i = 0;
			foreach ( $post['khv_feeds'] as $f ) {
				$i++;
				if ( $i > 500 ) {
					break;
				}
				if ( ! is_array( $f ) ) {
					continue;
				}
				$url = isset( $f['url'] ) ? esc_url_raw( trim( $f['url'] ) ) : '';
				if ( $url === '' || ! preg_match( '#^https?://#i', $url ) ) {
					continue;
				}
				$cat = isset( $f['category'] ) ? absint( $f['category'] ) : 0;
				if ( $cat !== 0 && ! term_exists( $cat, 'category' ) ) {
					$cat = 0;
				}
				$name = isset( $f['name'] ) ? trim( (string) $f['name'] ) : '';
				$feeds[] = array(
					'name'      => $name !== '' ? sanitize_text_field( $name ) : 'فید ' . $i,
					'url'       => $url,
					'category'  => $cat,
					'enabled'   => ! empty( $f['enabled'] ) ? 1 : 0,
					'max_items' => isset( $f['max_items'] ) ? min( 50, max( 1, absint( $f['max_items'] ) ) ) : 5,
				);
			}
		}

		$settings = array(
			'api_url'        => $api_url,
			'api_key'        => $api_key,
			'ai_model'       => $ai_model,
			'feeds'          => $feeds,
			'post_type'      => ( isset( $post['post_type'] ) && in_array( $post['post_type'], self::post_type_choices(), true ) ) ? $post['post_type'] : 'post',
			'post_status'    => ( isset( $post['post_status'] ) && in_array( $post['post_status'], array( 'draft', 'publish', 'pending' ), true ) ) ? $post['post_status'] : 'draft',
			'image_mode'     => ( isset( $post['image_mode'] ) && in_array( $post['image_mode'], array( 'remote', 'download' ), true ) ) ? $post['image_mode'] : 'remote',
			'keywords_mode'  => ( isset( $post['keywords_mode'] ) && in_array( $post['keywords_mode'], array( 'tags', 'yoast', 'rankmath', 'meta' ), true ) ) ? $post['keywords_mode'] : 'tags',
			'attribution'    => ( isset( $post['attribution'] ) && in_array( $post['attribution'], array( 'none', 'line' ), true ) ) ? $post['attribution'] : 'none',
			'interval'       => ( isset( $post['interval'] ) && in_array( $post['interval'], array( 'khv_5min', 'khv_15min', 'khv_30min', 'hourly', 'daily', 'manual' ), true ) ) ? $post['interval'] : 'khv_5min',
			'scan_batches'   => isset( $post['scan_batches'] ) ? min( 20, max( 1, absint( $post['scan_batches'] ) ) ) : $d['scan_batches'],
			'skip_cats'      => isset( $post['skip_cats'] ) ? sanitize_text_field( $post['skip_cats'] ) : $d['skip_cats'],
			'max_text_chars' => isset( $post['max_text_chars'] ) ? min( 20000, max( 500, absint( $post['max_text_chars'] ) ) ) : $d['max_text_chars'],
			'max_items_per_run' => isset( $post['max_items_per_run'] ) ? min( 200, max( 1, absint( $post['max_items_per_run'] ) ) ) : $d['max_items_per_run'],
		);

		self::save_array( $settings );

		return $settings;
	}
}
