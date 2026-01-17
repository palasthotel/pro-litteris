<?php


namespace Palasthotel\ProLitteris\Model;

use Palasthotel\ProLitteris\Plugin;
use WP_Error;

class FetchPixelsResponse extends _BaseAPIResponse {

	/**
	 * @var Pixel[]
	 */
	var $pixels = [];

	public function __construct( string $response ) {
		parent::__construct( $response );

		if ( $response instanceof WP_Error ) {
			return;
		}

		if ( empty( $this->data->domain ) || ! is_array( $this->data->pixelUids ) || count( $this->data->pixelUids ) <= 0 ) {
			$this->error = new WP_Error( Plugin::ERROR_CODE_RESPONSE, "Unbekannte Antwort: " . $response );

			return;
		}

		$domain       = $this->data->domain;
		$this->pixels = array_map( function ( $uid ) use ( $domain ) {
			return Pixel::build( $domain, $uid );
		}, $this->data->pixelUids );
	}
}
