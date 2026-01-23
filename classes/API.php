<?php

namespace Palasthotel\ProLitteris;

use Palasthotel\ProLitteris\Model\FetchPixelsResponse;
use Palasthotel\ProLitteris\Model\PushMessageResponse;
use WP_Error;

class API {

	/**
	 * @param int $amount
	 *
	 * @return FetchPixelsResponse|WP_Error
	 */
	public function fetchPixels( $amount = 20 ) {

		$requestPath = "/rest/api/1/pixel";

		$response = $this->request( $requestPath, array( "amount" => $amount ) );

		if ( $response instanceof WP_Error ) {
			return $response;
		}

		if ( empty( $response ) ) {
			return new WP_Error(
				Plugin::ERROR_CODE_REQUEST,
				__("Empty response on $response request with about => $amount", Plugin::DOMAIN)
			);
		}

		return new FetchPixelsResponse($response);
	}

	/**
	 * @param mixed $message
	 *
	 * @return PushMessageResponse|WP_Error
	 */
	public function pushMessage( $message, $post_id ) {
		$response = $this->request("/rest/api/1/message", $message);
		if($response instanceof WP_Error) return $response;

		return new PushMessageResponse($response, $post_id);
	}

	/**
	 * @param string $path
	 * @param mixed $body
	 *
	 * @return string|WP_Error
	 */
	private function request( string $path, $body ) {

		if ( ! defined( 'PH_PRO_LITTERIS_CREDENTIALS' ) ) {
			return new WP_Error( Plugin::ERROR_CODE_CONFIG, "Missing ProLitteris credentials" );
		}

		if ( ! defined( 'PH_PRO_LITTERIS_SYSTEM' ) ) {
			return new WP_Error( Plugin::ERROR_CODE_CONFIG, "Missing ProLitteris system url" );
		}

		$headers = array(
			"Content-Type"  => "application/json; charset=utf-8",
			"Authorization" => "OWEN " . base64_encode( PH_PRO_LITTERIS_CREDENTIALS )
		);

		$args     = array(
			"headers"   => $headers,
			"body"      => json_encode( $body ),
			"sslverify" => false,
		);
		$response = wp_remote_post(
			PH_PRO_LITTERIS_SYSTEM . $path,
			$args
		);
		if ( $response instanceof WP_Error ) {
			return $response;
		}
		$body = wp_remote_retrieve_body(
			$response
		);

		return $body;
	}

}
