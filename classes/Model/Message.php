<?php


namespace Palasthotel\ProLitteris\Model;


class Message {

	/**
	 * @var string
	 */
	var $pixelUid;
	/**
	 * @var string
	 */
	var $title = "";
	/**
	 * @var string
	 */
	var $plaintext = "";
	/**
	 * @var array
	 */
	var $participants = [];
	/**
	 * @var null|int
	 */
	var $reported = null;

	/**
	 * @var null|array
	 */
	var $response = null;

	public function __construct(string $pixelUid) {
		$this->pixelUid = $pixelUid;
	}
}
