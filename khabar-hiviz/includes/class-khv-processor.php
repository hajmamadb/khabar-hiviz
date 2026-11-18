<?php
/**
 * پردازش اصلی: اسکن فیدها → صف انتظار → هوش مصنوعی → ساخت پیش‌نویس.
 *
 * معماری صف‌دار (الگوبرداری از DEMO TITR):
 * - scan_feeds(): فیدها را چک می‌کند و خبرهای جدید را در صف (با زمان انتشار) قرار می‌دهد.
 * - process_queue(): هر تیک، تعداد محدودی از صف را به‌ترتیب زمان انتشار پردازش می‌کند تا
 *   هاست‌های معمولی با timeout مواجه نشوند.
 * - خطاهای موقت با فاصلهٔ افزایشی (exponential backoff) دوباره امتحان می‌شوند.
 * - بعد از ۳ تلاش ناموفق از هوش مصنوعی، خبر با متن اصلی (پاک‌سازی‌شده محلی) درج می‌شود.
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KHV_Processor {

	const PROCESSED_OPTION = 'khv_processed';
	const QUEUE_OPTION     = 'khv_queue';
	const CUTOFF_OPTION    = 'khv_cutoff';
	const PAUSED_OPTION    = 'khv_paused';

	const MAX_ATTEMPTS = 5;    // بعد از این تعداد تلاش، خبر از صف حذف می‌شود.
	const MIN_TEXT_LEN = 300;  // حداقل طول متن قابل‌پذیر (کاراکتر).

	// ---------- صف، نقطه شروع و توقف ----------

	/**
	 * خواندن صف انتظار.
	 *
	 * @return array
	 */
	public static function get_queue() {
		$q = get_option( self::QUEUE_OPTION, array() );
		return is_array( $q ) ? $q : array();
	}

	/**
	 * ذخیره صف انتظار (حداکثر ۵۰۰ مورد).
	 *
	 * @param array $queue آرایه صف.
	 */
	public static function save_queue( $queue ) {
		$queue = (array) $queue;
		if ( count( $queue ) > 500 ) {
			$queue = array_slice( $queue, 0, 500 );
		}
		update_option( self::QUEUE_OPTION, $queue, false );
	}

	/**
	 * تعداد خبرهای صف.
	 *
	 * @return int
	 */
	public static function queue_count() {
		return count( self::get_queue() );
	}

	/**
	 * پاک‌کردن صف.
	 */
	public static function clear_queue() {
		delete_option( self::QUEUE_OPTION );
	}

	/**
	 * زمان نقطه شروع دریافت اخبار (0 = تعیین‌نشده).
	 *
	 * @return int
	 */
	public static function get_cutoff() {
		return (int) get_option( self::CUTOFF_OPTION, 0 );
	}

	/**
	 * ثبت نقطه شروع جدید.
	 *
	 * @param int $ts زمان (unix).
	 */
	public static function set_cutoff( $ts ) {
		update_option( self::CUTOFF_OPTION, (int) $ts, false );
	}

	/**
	 * آیا پردازش متوقف شده است؟
	 *
	 * @return bool
	 */
	public static function is_paused() {
		return (bool) get_option( self::PAUSED_OPTION, false );
	}

	/**
	 * توقف / ادامه پردازش.
	 *
	 * @param bool $paused وضعیت.
	 */
	public static function set_paused( $paused ) {
		update_option( self::PAUSED_OPTION, (bool) $paused, false );
	}

	// ---------- اسکن فیدها ----------

	/**
	 * چک همه فیدهای فعال و افزودن خبرهای تازه به صف.
	 *
	 * @return array { added: int, feeds: int, errors: int, ignored_before_cutoff: int }
	 */
	public static function scan_feeds() {
		$settings = KHV_Settings::get();
		$summary  = array(
			'added'                 => 0,
			'feeds'                 => 0,
			'errors'                => 0,
			'ignored_before_cutoff' => 0,
		);

		$queue       = self::get_queue();
		$queued_keys = array();
		foreach ( $queue as $q ) {
			if ( ! empty( $q['key'] ) ) {
				$queued_keys[ $q['key'] ] = true;
			}
		}
		$processed = self::get_processed();
		$cutoff    = self::get_cutoff();

		// اسکن گروهی (چرخشی): هر تیک فقط یک گروه از فیدها چک می‌شود تا با
		// تعداد زیاد فید (صدها فید)، زمان هر اجرا منطقی بماند.
		$enabled = array();
		foreach ( $settings['feeds'] as $feed ) {
			if ( ! empty( $feed['enabled'] ) && ! empty( $feed['url'] ) ) {
				$enabled[] = $feed;
			}
		}

		$batches = isset( $settings['scan_batches'] ) ? min( 20, max( 1, (int) $settings['scan_batches'] ) ) : 1;
		$batch   = 0;
		if ( $batches > 1 && ! empty( $enabled ) ) {
			$batch = (int) get_option( 'khv_scan_cursor', 0 ) % $batches;
			$size  = (int) ceil( count( $enabled ) / $batches );
			$feeds = array_slice( $enabled, $batch * $size, $size );
			update_option( 'khv_scan_cursor', ( $batch + 1 ) % $batches, false );
		} else {
			$feeds = $enabled;
		}

		foreach ( $feeds as $feed ) {
			$summary['feeds']++;
			$feed_name = ! empty( $feed['name'] ) ? $feed['name'] : $feed['url'];

			$res = KHV_Feeds::fetch( $feed['url'], 15 );
			if ( isset( $res['error'] ) ) {
				KHV_Log::add( 'خطا در دریافت فید «' . $feed_name . '»: ' . $res['error'], 'error' );
				$summary['errors']++;
				continue;
			}

			$per_feed = max( 1, (int) $feed['max_items'] );
			$skip     = array_filter( array_map( 'trim', explode( ',', (string) $settings['skip_cats'] ) ) );
			$added    = 0;

			foreach ( $res['items'] as $item ) {
				if ( $added >= $per_feed ) {
					break;
				}
				if ( empty( $item['link'] ) ) {
					continue;
				}

				// رد آیتم‌هایی که دسته‌شان با skip_cats یکی است (مثلا «ویدیو»)
				$cat_text = implode( ' | ', (array) $item['categories'] );
				$skip_item = false;
				foreach ( $skip as $s ) {
					if ( $s !== '' && mb_strpos( $cat_text, $s ) !== false ) {
						$skip_item = true;
						break;
					}
				}
				if ( $skip_item ) {
					continue;
				}

				$canonical = self::canonical_link( $item['link'] );
				$key       = md5( $canonical );

				if ( isset( $queued_keys[ $key ] ) || isset( $processed[ $key ] ) ) {
					continue;
				}

				$published_ts = $item['pubDate'] !== '' ? (int) strtotime( $item['pubDate'] ) : 0;
				if ( $cutoff && ( $published_ts <= 0 || $published_ts <= $cutoff ) ) {
					$summary['ignored_before_cutoff']++;
					continue;
				}

				$queue[] = array(
					'key'            => $key,
					'title'          => $item['title'],
					'link'           => $item['link'],
					'canonical'      => $canonical,
					'published_ts'   => $published_ts,
					'description'    => $item['description'],
					'content'        => $item['content'],
					'enclosure'      => $item['enclosure'],
					'source'         => $item['source'],
					'categories'     => (array) $item['categories'],
					'feed_name'      => $feed_name,
					'feed_url'       => $feed['url'],
					'feed_category'  => isset( $feed['category'] ) ? (int) $feed['category'] : 0,
					'attempts'       => 0,
					'next_attempt_at' => 0,
					'queued_at'      => time(),
				);
				$queued_keys[ $key ] = true;
				$added++;
			}

			if ( $added > 0 ) {
				$summary['added'] += $added;
				KHV_Log::add( 'فید «' . $feed_name . '»: ' . $added . ' خبر تازه به صف اضافه شد.', 'info' );
			}
		}

		if ( $queue ) {
			usort(
				$queue,
				function ( $a, $b ) {
					return (int) $b['published_ts'] <=> (int) $a['published_ts'];
				}
			);
		}
		self::save_queue( $queue );

		$msg = 'بررسی فیدها تمام شد؛ ' . $summary['added'] . ' خبر جدید به صف اضافه شد.';
		if ( $batches > 1 ) {
			$msg .= ' (گروه ' . ( $batch + 1 ) . ' از ' . $batches . ')';
		}
		if ( $summary['ignored_before_cutoff'] > 0 ) {
			$msg .= ' ' . $summary['ignored_before_cutoff'] . ' خبر قدیمی‌تر از نقطه شروع نادیده گرفته شد.';
		}
		KHV_Log::add( $msg, 'info' );

		return $summary;
	}

	// ---------- پردازش صف ----------

	/**
	 * پردازش تعداد محدودی از صف (هر تیک).
	 *
	 * @param int|null $limit حداکثر تعداد (پیش‌فرض: max_items_per_run تنظیمات).
	 * @return array { created: int, errors: int, retries: int, dropped: int, processed: int }
	 */
	public static function process_queue( $limit = null ) {
		$settings = KHV_Settings::get();
		if ( $limit === null ) {
			$limit = isset( $settings['max_items_per_run'] ) ? max( 1, (int) $settings['max_items_per_run'] ) : 3;
		}

		$summary = array( 'created' => 0, 'errors' => 0, 'retries' => 0, 'dropped' => 0, 'processed' => 0 );
		$queue   = self::get_queue();
		if ( ! $queue ) {
			return $summary;
		}

		$now       = time();
		$cutoff    = self::get_cutoff();
		$processed = self::get_processed();
		$remaining = $limit;
		$new_queue = array();

		foreach ( $queue as $item ) {
			if ( $remaining <= 0 || ( isset( $item['next_attempt_at'] ) && (int) $item['next_attempt_at'] > $now ) ) {
				$new_queue[] = $item;
				continue;
			}
			$remaining--;
			$summary['processed']++;

			// نقطه شروع تغییر کرده و خبر قدیمی‌تر از آن است.
			$item_ts = isset( $item['published_ts'] ) ? (int) $item['published_ts'] : 0;
			if ( $cutoff && ( $item_ts <= 0 || $item_ts <= $cutoff ) ) {
				$summary['dropped']++;
				KHV_Log::add( 'خبر قدیمی از صف کنار گذاشته شد (قدیمی‌تر از نقطه شروع): ' . $item['title'], 'warn' );
				continue;
			}

			$r = self::process_item( $item, $settings );

			if ( 'created' === $r['status'] ) {
				$processed[ $item['key'] ] = array(
					'post_id' => (int) $r['post_id'],
					'time'    => time(),
				);
				$summary['created']++;
				KHV_Log::add( 'پیش‌نویس ساخته شد: ' . $r['title'] . ' (شناسه پست: ' . $r['post_id'] . ')', 'info' );
				continue;
			}

			if ( 'duplicate' === $r['status'] ) {
				$processed[ $item['key'] ] = array(
					'time'    => time(),
					'skipped' => 'exists',
				);
				KHV_Log::add( 'خبر تکراری بود و پیش‌نویس جدید ساخته نشد: ' . $item['title'], 'info' );
				continue;
			}

			if ( 'dropped' === $r['status'] ) {
				$summary['dropped']++;
				KHV_Log::add( 'خبر از صف حذف شد (بدون درج): ' . $item['title'] . ' — ' . $r['error'], 'error' );
				continue;
			}

			// retry
			$item['attempts'] = ( isset( $item['attempts'] ) ? (int) $item['attempts'] : 0 ) + 1;
			if ( isset( $r['error'] ) ) {
				$item['last_error'] = mb_substr( $r['error'], 0, 300 );
			}
			$item['next_attempt_at'] = $now + min( 1800, 60 * ( 2 ** min( 5, max( 0, $item['attempts'] - 1 ) ) ) );

			if ( $item['attempts'] >= self::MAX_ATTEMPTS ) {
				$summary['dropped']++;
				KHV_Log::add( 'بعد از ' . $item['attempts'] . ' تلاش، خبر از صف حذف شد: ' . $item['title'] . ' — آخرین خطا: ' . ( isset( $item['last_error'] ) ? $item['last_error'] : 'نامشخص' ), 'error' );
			} else {
				$new_queue[] = $item;
				$summary['retries']++;
				KHV_Log::add( 'خبر به صف برگشت (تلاش ' . $item['attempts'] . ' از ' . self::MAX_ATTEMPTS . '): ' . $item['title'], 'warn' );
			}
		}

		self::save_queue( $new_queue );
		self::save_processed( $processed );

		return $summary;
	}

	/**
	 * پردازش کامل یک خبر (صفحه منبع → AI → پیش‌نویس).
	 *
	 * @param array $item     آیتم صف.
	 * @param array $settings تنظیمات.
	 * @return array { status: created|duplicate|retry|dropped, post_id?: int, title?: string, error?: string }
	 */
	private static function process_item( $item, $settings ) {
		// تکراری‌بودن (مثلا پیش‌نویس دستی)
		if ( self::post_exists_by_source( $item['canonical'], $settings['post_type'] ) ) {
			return array( 'status' => 'duplicate' );
		}

		// --- استخراج متن کامل خبر از صفحه اصلی ---
		$text  = '';
		$image = isset( $item['enclosure'] ) ? $item['enclosure'] : '';

		$art = KHV_Article::fetch( $item['link'] );
		if ( empty( $art['error'] ) ) {
			$ex = KHV_Article::extract( $art['html'] );
			if ( $ex['text'] !== '' && mb_strlen( $ex['text'] ) >= self::MIN_TEXT_LEN ) {
				$text = $ex['text'];
			}
			if ( $image === '' && $ex['image'] !== '' ) {
				$image = $ex['image'];
			}
		}

		// اگر متن کامل به‌دست نیامد، از توضیح/محتوای فید استفاده کن
		if ( mb_strlen( $text ) < self::MIN_TEXT_LEN ) {
			$alt = $item['description'] !== '' ? $item['description'] : $item['content'];
			if ( mb_strlen( $alt ) >= self::MIN_TEXT_LEN ) {
				$text = $alt;
			}
		}

		if ( mb_strlen( $text ) < self::MIN_TEXT_LEN ) {
			return array( 'status' => 'retry', 'error' => 'متن کامل خبر به‌دست نیامد (طول قابل‌پذیر: ' . self::MIN_TEXT_LEN . ' کاراکتر)' );
		}

		$text = mb_substr( $text, 0, (int) $settings['max_text_chars'] );

		// --- هوش مصنوعی ---
		$date = isset( $item['published_ts'] ) && $item['published_ts'] > 0 ? gmdate( 'D, d M Y H:i:s T', $item['published_ts'] ) : '';
		$attempts = isset( $item['attempts'] ) ? (int) $item['attempts'] : 0;

		$ai = KHV_AI::process_article( $settings, $item['title'], $text, $date );

		if ( ! $ai['ok'] || $ai['body'] === '' ) {
			$err = isset( $ai['error'] ) ? $ai['error'] : 'خروجی خالی';
			// بعد از ۳ تلاش ناموفق از هوش مصنوعی، با متن اصلی درج می‌شود.
			if ( $attempts >= 2 ) {
				KHV_Log::add( 'هوش مصنوعی بعد از ' . ( $attempts + 1 ) . ' تلاش در دسترس نبود؛ «' . $item['title'] . '» با متن اصلی درج می‌شود.', 'warn' );
				$ai = array(
					'ok'       => true,
					'title'    => $item['title'],
					'lead'     => '',
					'body'     => self::local_clean( $text ),
					'keywords' => array(),
				);
			} else {
				return array( 'status' => 'retry', 'error' => 'هوش مصنوعی: ' . $err );
			}
		}

		// --- ساخت پیش‌نویس ---
		$post_id = self::create_post( $settings, $item, $ai, $image );
		if ( is_wp_error( $post_id ) ) {
			return array( 'status' => 'retry', 'error' => 'خطا در ساخت پست: ' . $post_id->get_error_message() );
		}

		return array(
			'status'  => 'created',
			'post_id' => (int) $post_id,
			'title'   => $ai['title'],
		);
	}

	/**
	 * اجرای فوری: اسکن + پردازش صف تا خالی‌شدن یا پایان زمان مجاز.
	 *
	 * @return array خلاصه کل اجرا.
	 */
	public static function run_now() {
		$scan = self::scan_feeds();

		$summary = array(
			'created' => 0,
			'feeds'   => $scan['feeds'],
			'errors'  => $scan['errors'],
		);

		$deadline = time() + 300; // حداکثر ۵ دقیقه برای کل اجرا.
		$loops    = 0;
		while ( $loops < 60 && time() < $deadline && self::queue_count() > 0 ) {
			$r      = self::process_queue( 1 );
			$loops++;
			$summary['created'] += $r['created'];
			$summary['errors']  += $r['errors'];
		}

		if ( self::queue_count() > 0 ) {
			KHV_Log::add( 'اجرای فوری پایان یافت؛ ' . self::queue_count() . ' خبر هنوز در صف است و در اجراهای بعد پردازش می‌شود.', 'info' );
		}

		return $summary;
	}

	// ---------- ابزارها ----------

	/**
	 * لینک کانونیکال خبر؛ برای خبرورزشی/خبرآنلاین به شکل https://site.com/news/123456/
	 *
	 * @param string $link لینک خام فید.
	 * @return string
	 */
	public static function canonical_link( $link ) {
		$link = trim( (string) $link );
		if ( preg_match( '#^(https?://[^/]+/news/(\d+))(?:/|$)#i', $link, $m ) ) {
			return $m[1] . '/';
		}
		return $link;
	}

	/**
	 * بررسی وجود پستی با آدرس منبع یکسان.
	 *
	 * @param string $url       آدرس منبع.
	 * @param string $post_type نوع پست.
	 * @return bool
	 */
	private static function post_exists_by_source( $url, $post_type ) {
		$ids = get_posts(
			array(
				'post_type'   => 'any',
				'post_status' => 'any',
				'meta_key'    => '_khv_source_url',
				'meta_value'  => $url,
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		return ! empty( $ids );
	}

	/**
	 * ساخت پست پیش‌نویس (با لید، بدنه، کلیدواژه، تصویر و متا).
	 *
	 * @param array  $settings تنظیمات.
	 * @param array  $item     آیتم صف.
	 * @param array  $ai       نتیجه هوش مصنوعی.
	 * @param string $image    آدرس تصویر.
	 * @return int|WP_Error
	 */
	private static function create_post( $settings, $item, $ai, $image ) {
		$feed = array(
			'name'     => $item['feed_name'],
			'category' => $item['feed_category'],
		);

		// لید به‌عنوان پاراگراف اول (پررنگ)
		$html = '';
		if ( ! empty( $ai['lead'] ) ) {
			$html .= '<p><strong>' . esc_html( $ai['lead'] ) . '</strong></p>';
		}

		// بدنه را به پاراگراف تبدیل کن
		$paragraphs = preg_split( '/\n\s*\n/u', $ai['body'] );
		foreach ( (array) $paragraphs as $p ) {
			$p = trim( $p );
			if ( $p !== '' ) {
				$html .= '<p>' . esc_html( $p ) . '</p>';
			}
		}
		if ( $html === '' ) {
			return new WP_Error( 'khv_no_content', 'محتوای نهایی خالی بود' );
		}

		if ( $settings['attribution'] === 'line' ) {
			$src_name = $item['source'] !== '' ? $item['source'] : $item['feed_name'];
			$html    .= '<p class="khv-source">منبع: <a href="' . esc_url( $item['canonical'] ) . '" target="_blank" rel="nofollow noopener">' . esc_html( $src_name ) . '</a></p>';
		}

		$postarr = array(
			'post_title'   => wp_strip_all_tags( $ai['title'] ),
			'post_content' => $html,
			'post_excerpt' => ! empty( $ai['lead'] ) ? wp_strip_all_tags( $ai['lead'] ) : '',
			'post_status'  => $settings['post_status'],
			'post_type'    => $settings['post_type'],
			'ping_status'  => 'closed',
		);

		$taxonomies = (array) get_object_taxonomies( $settings['post_type'] );
		if ( in_array( 'category', $taxonomies, true ) && ! empty( $feed['category'] ) ) {
			$postarr['post_category'] = array( (int) $feed['category'] );
		}

		$post_id = wp_insert_post( $postarr, true );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		// --- کلیدواژه‌ها ---
		$kw = (array) $ai['keywords'];
		switch ( $settings['keywords_mode'] ) {
			case 'yoast':
				if ( ! empty( $kw ) ) {
					update_post_meta( $post_id, '_yoast_wpseo_focus_keyword', $kw[0] );
				}
				break;
			case 'rankmath':
				if ( ! empty( $kw ) ) {
					update_post_meta( $post_id, '_rank_math_focus_keyword', $kw[0] );
				}
				break;
			case 'meta':
				if ( ! empty( $kw ) ) {
					update_post_meta( $post_id, '_khv_keywords', implode( ', ', $kw ) );
				}
				break;
			case 'tags':
			default:
				if ( ! empty( $kw ) && in_array( 'post_tag', $taxonomies, true ) ) {
					wp_set_object_terms( $post_id, $kw, 'post_tag' );
				}
				break;
		}

		// --- تصویر ---
		self::set_image( $settings, $post_id, (string) $image );

		// --- متاها ---
		update_post_meta( $post_id, '_khv_source_url', $item['canonical'] );
		update_post_meta( $post_id, '_khv_feed_name', $item['feed_name'] );
		update_post_meta( $post_id, '_khv_original_title', $item['title'] );
		if ( $item['source'] !== '' ) {
			update_post_meta( $post_id, '_khv_source_name', $item['source'] );
		}

		return (int) $post_id;
	}

	/**
	 * تنظیم تصویر شاخص (از راه دور یا دانلود).
	 *
	 * @param array  $settings تنظیمات.
	 * @param int    $post_id  شناسه پست.
	 * @param string $url      آدرس تصویر.
	 */
	private static function set_image( $settings, $post_id, $url ) {
		$url = trim( $url );
		if ( $url === '' || ! preg_match( '#^https?://#i', $url ) ) {
			return;
		}

		if ( $settings['image_mode'] === 'download' ) {
			self::image_download( $post_id, $url );
		} else {
			self::image_remote( $post_id, $url );
		}
	}

	/**
	 * ثبت تصویر به‌صورت attachment با آدرس خارجی (بدون دانلود).
	 *
	 * @param int    $post_id شناسه پست.
	 * @param string $url     آدرس تصویر.
	 */
	private static function image_remote( $post_id, $url ) {
		$path = (string) parse_url( $url, PHP_URL_PATH );
		$name = basename( $path );
		if ( $name === '' ) {
			$name = 'news-image.jpg';
		}

		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		$mime = 'image/jpeg';
		if ( 'png' === $ext ) {
			$mime = 'image/png';
		} elseif ( 'gif' === $ext ) {
			$mime = 'image/gif';
		} elseif ( 'webp' === $ext ) {
			$mime = 'image/webp';
		}

		$att = wp_insert_attachment(
			array(
				'post_title'     => sanitize_file_name( $name ),
				'post_content'   => esc_url_raw( $url ),
				'post_status'    => 'inherit',
				'post_type'      => 'attachment',
				'post_mime_type' => $mime,
			),
			$url,
			$post_id
		);

		if ( $att && ! is_wp_error( $att ) ) {
			set_post_thumbnail( $post_id, $att );
		}
	}

	/**
	 * دانلود تصویر و ثبت در کتابخانه رسانه.
	 *
	 * @param int    $post_id شناسه پست.
	 * @param string $url     آدرس تصویر.
	 */
	private static function image_download( $post_id, $url ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$resp = wp_safe_remote_get(
			$url,
			array(
				'timeout'     => 60,
				'redirection' => 5,
				'headers'     => array(
					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
					'Referer'    => 'https://www.google.com/',
				),
			)
		);

		if ( is_wp_error( $resp ) ) {
			KHV_Log::add( 'خطا در دانلود تصویر: ' . $resp->get_error_message(), 'warn' );
			return;
		}

		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = (string) wp_remote_retrieve_body( $resp );
		if ( $code !== 200 || $body === '' ) {
			KHV_Log::add( 'خطا در دانلود تصویر (HTTP ' . $code . '): ' . $url, 'warn' );
			return;
		}

		$path     = (string) parse_url( $url, PHP_URL_PATH );
		$filename = basename( $path );
		if ( $filename === '' ) {
			$filename = 'news-image.jpg';
		}

		$tmp = wp_tempnam( $filename );
		if ( ! $tmp ) {
			return;
		}
		file_put_contents( $tmp, $body );

		$att = media_handle_sideload(
			array(
				'name'     => $filename,
				'tmp_name' => $tmp,
			),
			$post_id
		);

		@unlink( $tmp );

		if ( is_wp_error( $att ) ) {
			KHV_Log::add( 'خطا در پیوست تصویر: ' . $att->get_error_message(), 'warn' );
			return;
		}

		set_post_thumbnail( $post_id, $att );
	}

	/**
	 * پاک‌سازی محلی متن وقتی هوش مصنوعی در دسترس نباشد (حذف «به گزارش ...»).
	 *
	 * @param string $text متن.
	 * @return string
	 */
	private static function local_clean( $text ) {
		$text = preg_replace( '/(^|[\n.!?؟،,;؛]\s*)به\s+گزارش\s+[^\n.!?؟]+/u', '$1', $text );
		$text = preg_replace( '/(^|[\n.!?؟،,;؛]\s*)بر\s+اساس\s+گزارش\s+[^\n.!?؟]+/u', '$1', $text );
		$text = preg_replace( '/(^|[\n.!?؟،,;؛]\s*)طبق\s+گزارش\s+[^\n.!?؟]+/u', '$1', $text );
		$text = preg_replace( '/(^|[\n.!?؟،,;؛]\s*)بنابر\s+روایت\s+[^\n.!?؟]+/u', '$1', $text );
		return trim( $text );
	}

	/**
	 * خواندن آرایه خبرهای پردازش‌شده.
	 *
	 * @return array
	 */
	public static function get_processed() {
		$p = get_option( self::PROCESSED_OPTION, array() );
		return is_array( $p ) ? $p : array();
	}

	/**
	 * ذخیره آرایه خبرهای پردازش‌شده (حداکثر ۲۰۰۰ مورد).
	 *
	 * @param array $processed آرایه.
	 */
	public static function save_processed( $processed ) {
		$processed = (array) $processed;
		if ( count( $processed ) > 2000 ) {
			$processed = array_slice( $processed, -2000 );
		}
		update_option( self::PROCESSED_OPTION, $processed, false );
	}
}
