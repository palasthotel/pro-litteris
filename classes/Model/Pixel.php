<?php


namespace Palasthotel\ProLitteris\Model;


use Palasthotel\ProLitteris\Plugin;

/**
 * @property string domain
 * @property string uid
 * @property int|null post_id
 */
class Pixel {

	/**
	 * Pixel constructor.
	 *
	 * @param string $domain
	 * @param string $uid
	 * @param int|string|null $post_id
	 */
	private function __construct(string $domain, string $uid, $post_id) {
		$this->domain = $domain;
		$this->uid = $uid;
		$this->post_id = $post_id;
	}

	public static function build(string $domain, string $uid, $post_id = null): Pixel {
		return new self($domain, $uid, $post_id);
	}

	public function toUrl(): string {
		$hasPaywall = apply_filters(Plugin::FILTER_POST_HAS_PAYWALL, false, $this->post_id);
		$ns = $hasPaywall ? "pw" : "na";
		return "https://".$this->domain."/$ns/".$this->uid;
	}
}
