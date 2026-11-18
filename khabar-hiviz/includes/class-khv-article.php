<?php
/**
 * دریافت صفحه خبر و استخراج متن کامل و تصویر از آن.
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KHV_Article {

	/**
	 * دریافت HTML صفحه.
	 *
	 * @param string $url     آدرس صفحه.
	 * @param int    $timeout مهلت (ثانیه).
	 * @return array { error?: string, html?: string }
	 */
	public static function fetch( $url, $timeout = 30 ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => $timeout,
				'redirection' => 5,
				'headers'     => array(
					'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
					'Accept-Language' => 'fa,en;q=0.8',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array( 'error' => $response->get_error_message() );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = (string) wp_remote_retrieve_body( $response );

		if ( $code !== 200 || $body === '' ) {
			return array( 'error' => 'HTTP ' . $code );
		}

		return array( 'html' => $body );
	}

	/**
	 * استخراج متن کامل و تصویر از HTML.
	 *
	 * @param string $html محتوای صفحه.
	 * @return array { text: string, image: string }
	 */
	public static function extract( $html ) {
		return array(
			'text'  => self::extract_text( $html ),
			'image' => self::extract_image( $html ),
		);
	}

	/**
	 * پیدا کردن بدنه خبر و تبدیل به متن خالص.
	 *
	 * @param string $html HTML صفحه.
	 * @return string
	 */
	public static function extract_text( $html ) {
		if ( $html === '' ) {
			return '';
		}

		// ۱) بهینه برای خبرورزشی و سایت‌های nastooh: itemprop="articleBody"
		$inner = self::capture_container( $html, '/<div[^>]*\bitemprop\s*=\s*["\']articleBody["\'][^>]*>/i' );
		if ( $inner !== '' && mb_strlen( self::html_to_text( $inner ) ) > 150 ) {
			return self::html_to_text( $inner );
		}

		// ۲) تگ article
		$inner = self::capture_container( $html, '/<article\b[^>]*>/i' );
		if ( $inner !== '' && mb_strlen( self::html_to_text( $inner ) ) > 300 ) {
			return self::html_to_text( $inner );
		}

		// ۳) div با کلاسی شامل text/body/story
		$inner = self::capture_container( $html, '/<div[^>]*class\s*=\s*["\'][^"\']*(?:article-body|news-body|story-body|post-body|text-detail|news-text)[^"\']*["\'][^>]*>/i' );
		if ( $inner !== '' && mb_strlen( self::html_to_text( $inner ) ) > 300 ) {
			return self::html_to_text( $inner );
		}

		// ۴) fallback: بلندترین بلوک پی در پی از تگ p
		if ( preg_match_all( '/<p[^>]*>([\s\S]*?)<\/p>/i', $html, $pm ) ) {
			$best = array();
			$cur  = array();
			foreach ( $pm[1] as $p ) {
				$t = trim( self::html_to_text( $p ) );
				if ( $t !== '' ) {
					$cur[] = $t;
					if ( count( $cur ) > count( $best ) ) {
						$best = $cur;
					}
				} else {
					$cur = array();
				}
			}
			if ( mb_strlen( implode( ' ', $best ) ) > 300 ) {
				return implode( "\n\n", $best );
			}
		}

		return '';
	}

	/**
	 * استخراج تصویر از og:image یا twitter:image.
	 *
	 * @param string $html HTML صفحه.
	 * @return string
	 */
	public static function extract_image( $html ) {
		$patterns = array(
			'/<meta[^>]+property\s*=\s*["\']og:image["\'][^>]+content\s*=\s*["\']([^"\']+)["\']/i',
			'/<meta[^>]+content\s*=\s*["\']([^"\']+)["\'][^>]+property\s*=\s*["\']og:image["\']/i',
			'/<meta[^>]+name\s*=\s*["\']twitter:image["\'][^>]+content\s*=\s*["\']([^"\']+)["\']/i',
		);

		foreach ( $patterns as $p ) {
			if ( preg_match( $p, $html, $m ) ) {
				$img = trim( $m[1] );
				if ( $img !== '' ) {
					// آدرس نسبی را با دامنه ترکیب نمی‌کنیم؛ فقط آدرس مطلق معتبر برمی‌گردانیم
					if ( preg_match( '#^https?://#i', $img ) ) {
						return $img;
					}
				}
			}
		}

		return '';
	}

	/**
	 * از HTML داده‌شده، محتوای درون یک تگ (به‌همراه فرزندان) را برمی‌گرداند.
	 *
	 * @param string $html    HTML کامل.
	 * @param string $pattern ریگ‌اکس تگ باز (باید یک capture group نداشته باشد).
	 * @return string
	 */
	private static function capture_container( $html, $pattern ) {
		if ( ! preg_match( $pattern, $html, $m, PREG_OFFSET_CAPTURE ) ) {
			return '';
		}

		$start = $m[0][1] + strlen( $m[0][0] );

		// نام تگ را از همان الگو استخراج می‌کنیم
		if ( ! preg_match( '/<([a-zA-Z][a-zA-Z0-9]*)/i', $m[0][0], $tagm ) ) {
			return '';
		}
		$tag = strtolower( $tagm[1] );

		// شمارش عمق تگ‌های هم‌نام تا تگ بسته پیدا شود
		$depth  = 1;
		$pos    = $start;
		$length = strlen( $html );

		while ( $depth > 0 && $pos < $length ) {
			$open_re  = '/<' . $tag . '(?:\s[^>]*)?>/i';
			$close_re = '/<\/' . $tag . '\s*>/i';

			preg_match( $open_re, $html, $o, PREG_OFFSET_CAPTURE, $pos );
			preg_match( $close_re, $html, $c, PREG_OFFSET_CAPTURE, $pos );

			$open_at  = isset( $o[0][1] ) ? (int) $o[0][1] : -1;
			$close_at = isset( $c[0][1] ) ? (int) $c[0][1] : -1;

			if ( $close_at === -1 ) {
				break;
			}
			if ( $open_at === -1 || $close_at < $open_at ) {
				$depth--;
				if ( $depth === 0 ) {
					return substr( $html, $start, $close_at - $start );
				}
				$pos = $close_at + strlen( $c[0][0] );
			} else {
				$depth++;
				$pos = $open_at + strlen( $o[0][0] );
			}
		}

		// اگر تگ بسته پیدا نشد، یک تکه محدود برگردان
		return substr( $html, $start, 15000 );
	}

	/**
	 * تبدیل HTML به متن خالص و تمیز.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public static function html_to_text( $html ) {
		if ( $html === '' ) {
			return '';
		}

		// حذف اسکریپت، استایل،_iframe_ و بخش‌های تبلیغاتی
		$html = preg_replace( '/<script\b[\s\S]*?<\/script\s*>/i', ' ', $html );
		$html = preg_replace( '/<style\b[\s\S]*?<\/style\s*>/i', ' ', $html );
		$html = preg_replace( '/<iframe\b[\s\S]*?<\/iframe\s*>/i', ' ', $html );
		$html = preg_replace( '/<aside\b[\s\S]*?<\/aside\s*>/i', ' ', $html );
		$html = preg_replace( '/<nav\b[\s\S]*?<\/nav\s*>/i', ' ', $html );
		$html = preg_replace( '/<footer\b[\s\S]*?<\/footer\s*>/i', ' ', $html );
		$html = preg_replace( '/<!--[\s\S]*?-->/i', ' ', $html );

		// حذف تگ‌های تبلیغاتی رایج
		$html = preg_replace( '/<[^>]*(?:class|id)\s*=\s*["\'][^"\']*(?:ads?|advertisement|banner|sponsored|related|sharing|share-|comment|comment)[^"\']*["\'][^>]*>[\s\S]*?<\/[a-z0-9]+>/i', ' ', $html );

		// تبدیل تگ‌های ختم بلوک به خط جدید
		$html = preg_replace( '/<\s*br\s*\/?\s*>/i', "\n", $html );
		$html = preg_replace( '/<\s*\/\s*(p|div|h[1-6]|li|tr|blockquote|section|article)\s*>/i', "\n", $html );

		// حذف همه تگ‌های باقی‌مانده
		$html = preg_replace( '/<[^>]*>/', ' ', $html );

		// تبدیل entityها
		$html = html_entity_decode( $html, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		// نرمال‌سازی فاصله‌ها
		$lines = explode( "\n", $html );
		$out   = array();
		foreach ( $lines as $line ) {
			$line = preg_replace( '/[^\S\n]+/u', ' ', $line );
			$line = trim( $line );
			$line = str_replace( "\u{200c}\u{200c}", "\u{200c}", $line );
			// رد خط‌های فقط عددی (شمارنده بازدید و امثال آن)
			if ( preg_match( '/^\d{2,}$/', $line ) ) {
				continue;
			}
			if ( $line !== '' ) {
				$out[] = $line;
			}
		}

		// ترکیب خط‌های کوتاه که تکه‌های یک پاراگراف بوده‌اند
		$paragraphs = array();
		$buf        = '';
		foreach ( $out as $line ) {
			if ( mb_strlen( $line ) < 60 && ( $buf !== '' ) && ( substr( $line, -1 ) !== '.' ) ) {
				$buf .= ' ' . $line;
			} else {
				if ( $buf !== '' ) {
					$paragraphs[] = $buf;
				}
				$buf = $line;
			}
		}
		if ( $buf !== '' ) {
			$paragraphs[] = $buf;
		}

		return implode( "\n\n", $paragraphs );
	}
}
