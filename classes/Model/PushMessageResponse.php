<?php

namespace Palasthotel\ProLitteris\Model;

use Palasthotel\ProLitteris\Plugin;
use WP_Error;

/**
 * @property Message|null message
 */
class PushMessageResponse extends _BaseAPIResponse {


	public function __construct( string $response, $post_id ) {
		parent::__construct( $response );

		if ( empty( $this->data->pixelUid ) || ! is_string( $this->data->pixelUid ) ) {

			$this->error = new WP_Error(
				Plugin::ERROR_CODE_RESPONSE,
				"No pixelUid in response.\n\n$response\n\npost_id: $post_id"
			);

			return;
		}

		if ( empty( $this->data->title ) || ! is_string( $this->data->title ) ) {

			$this->error = new WP_Error(
				Plugin::ERROR_CODE_RESPONSE,
				"No title in response.\n\n$response\n\npost_id: $post_id"
			);

			return;
		}

		if ( empty( $this->data->participants ) || ! is_array( $this->data->participants ) ) {

			$this->error = new WP_Error(
				Plugin::ERROR_CODE_RESPONSE,
				"No Participants in response.\n\n$response\n\npost_id: $post_id"
			);

			return;
		}

		$this->message = new Message($this->data->pixelUid);
		$this->message->title = $this->data->title;
		$this->message->participants = $this->data->participants;
		$this->message->reported = time();

	}
}
