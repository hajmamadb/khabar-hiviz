<?php
/**
 * دریافت و تفسیر فید RSS/Atom.
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class KHV_Feeds {

	/**
	 * دریافت فید و بازگرداندن آیتم‌ها.
	 *
	 * @param string $url     آدرس فید.
	 * @param int    $timeout مهلت درخواست (ثانیه).
	 * @return array { error?: string, items?: array }
	 */
	public static function fetch( $url, $timeout = 30 ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => $timeout,
				'redirection' => 5,
				'headers'     => array(
					'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
					'Accept'          => 'application/rss+xml, application/atom+xml, application/xml, text/xml, */*',
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

		$xml = simplexml_load_string( $body );
		if ( $xml === false ) {
			return array( 'error' => 'XML فید نامعتبر است' );
		}

		$namespaces = $xml->getNamespaces( true );
		$content_ns = isset( $namespaces['content'] ) ? $namespaces['content'] : 'http://purl.org/rss/1.0/modules/content/';

		$items = array();
		foreach ( $xml->xpath( '//item' ) as $item ) {
			$entry = array(
				'title'        => trim( (string) $item->title ),
				'link'         => trim( (string) $item->link ),
				'description'  => trim( (string) $item->description ),
				'pubDate'      => trim( (string) $item->pubDate ),
				'guid'         => trim( (string) $item->guid ),
				'categories'   => array(),
				'enclosure'    => '',
				'source'       => '',
				'source_url'   => '',
				'content'      => '',
			);

			foreach ( $item->category as $cat ) {
				$label = trim( (string) $cat );
				if ( $label !== '' ) {
					$entry['categories'][] = $label;
				}
			}

			$enc = $item->enclosure;
			if ( $enc instanceof SimpleXMLElement ) {
				$entry['enclosure'] = trim( (string) $enc['url'] );
			}

			$src = $item->source;
			if ( $src instanceof SimpleXMLElement ) {
				$entry['source']     = trim( (string) $src );
				$entry['source_url'] = trim( (string) $src['url'] );
			}

			try {
				$entry['content'] = trim( (string) $item->{ $content_ns }->encoded );
			} catch ( Exception $e ) {
				$entry['content'] = '';
			}

			if ( $entry['link'] === '' ) {
				$entry['link'] = $entry['guid'];
			}
			if ( $entry['link'] === '' ) {
				continue;
			}

			$items[] = $entry;
		}

		return array( 'items' => $items );
	}
}
