<?php

namespace Palasthotel\ProLitteris\Model;

use WP_Error;

/**
 * @property string raw
 */
class _BaseAPIResponse {

	/**
	 * @var string
	 */
	var $raw;

	/**
	 * @var WP_Error|null
	 */
	var $error = null;

	/**
	 * @var object|null
	 */
	var $data = null;

	/**
	 * _BaseAPIResponse constructor.
	 *
	 * @param string $response
	 */
	public function __construct( string $response ) {
		$this->raw  = $response;
		$this->data = json_decode( $response );
		if ( ! empty( $response->error ) ) {
			$this->error = new WP_Error( $this->data->error->code, $this->data->error->message );
		}
	}
}
