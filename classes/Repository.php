<?php


namespace Palasthotel\ProLitteris;

use Palasthotel\ProLitteris\Model\Pixel;
use Palasthotel\ProLitteris\Model\PreventPixelAssign;
use WP_Error;

/**
 * @property Database database
 * @property API api
 */
class Repository extends _Component {

	public function onCreate() {
		parent::onCreate();
		$this->api      = new API();
		$this->database = new Database();
	}

	/**
	 * @param int $desiredPoolSize
	 *
	 * @return int|WP_Error
	 */
	public function refillPixelPool( int $desiredPoolSize ) {
		$size              = $this->database->countAvailablePixels();
		$neededPixelsCount = $desiredPoolSize - $size;

		if ( $neededPixelsCount <= 0 ) {
			return 0;
		}


		$response = $this->api->fetchPixels( $neededPixelsCount );

		if ( $response instanceof WP_Error ) {
			$this->database->addAPIResponse(
				"",
				$response->get_error_message()
			);

			return $response;
		}

		if ( $response->error instanceof WP_Error ) {
			$this->database->addAPIResponse(
				$response->raw,
				$response->error->get_error_message()
			);

			return $response->error;
		}

		$this->database->addAPIResponse( $response->raw );

		$count = 0;
		foreach ( $response->pixels as $pixel ) {
			$result = $this->database->add( $pixel );
			if ( $result ) {
				$count += $result;
			}
		}

		return $count;

	}

	/**
	 * assign a pixel from pool to post
	 *
	 * @param int|string $post_id
	 *
	 * @return Pixel|WP_Error
	 */
	public function assignPixel( $post_id ) {

		/**
		 * @var PreventPixelAssign $preventAssign
		 */
		$preventAssign = apply_filters( Plugin::FILTER_PREVENT_PIXEL_ASSIGN, new PreventPixelAssign(), $post_id );

		if ( $preventAssign->prevent ) {
			return new WP_Error(
				Plugin::ERROR_CODE_ASSIGN_PIXEL,
				$preventAssign->message
			);
		}

		$this->database->assignPixel( $post_id );

		return $this->database->getPixel( $post_id );
	}

	/**
	 * @param int|string $post_id
	 *
	 * @param bool $autoAssign
	 *
	 * @return Pixel|WP_Error|null
	 */
	public function getPostPixel( $post_id, $autoAssign = false ) {
		$pixel = $this->database->getPixel( $post_id );
		if ( null !== $pixel || ! $autoAssign ) {
			return $pixel;
		}

		return $this->assignPixel( $post_id );
	}

	/**
	 * @param int|string $post_id
	 *
	 * @return bool|int|WP_Error
	 */
	public function pushPostMessage( $post_id ) {

		$pixel = $this->getPostPixel( $post_id );

		if ( $pixel instanceof WP_Error ) {
			return $pixel;
		}

		if ( null === $pixel ) {
			return new WP_Error(
				Plugin::ERROR_CODE_PUSH_MESSAGE,
				"Could not find a pixel for post $post_id"
			);
		}

		$message = $this->plugin->post->getPostMessage( $post_id );

		if ( $message instanceof WP_Error ) {
			return $message;
		}

		if ( ! MessageUtils::isMessageValid( $message ) ) {
			return new WP_Error(
				Plugin::ERROR_CODE_PUSH_MESSAGE,
				"Invalid message."
			);
		}

		$response = $this->plugin->api->pushMessage( $message, $post_id );

		if ( $response instanceof WP_Error ) {
			return $response;
		}

		if ( $response->error instanceof WP_Error ) {
			return $response->error;
		}

		$response->message->plaintext = $this->plugin->post->getPostText( $post_id );

		return $this->plugin->database->saveMessage( $response->message, get_current_user_id(), $response->raw );

	}

	/**
	 * @return bool
	 */
	public function isAutoMessagesEnabled() {
		return defined( 'PRO_LITTERIS_AUTO_MESSAGES' ) && PRO_LITTERIS_AUTO_MESSAGES === true;
	}

	/**
	 * automatically send messages
	 */
	public function autoMessages() {
		if ( ! $this->isAutoMessagesEnabled() ) {
			return;
		}
		$postIds = $this->database->getPostIdsReadyForMessage();
		foreach ( $postIds as $postId ) {
			$this->reportPost( $postId );
		}
	}

	/**
	 * @param $postId
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function reportPost( $postId, $force = false ) {
		$error = get_post_meta( $postId, Plugin::POST_META_PUSH_MESSAGE_ERROR, true );
		if ( !$force && ! empty( $error ) ) {
			return false;
		}

		$result = $this->pushPostMessage( $postId );
		if ( $result instanceof WP_Error ) {
			error_log( $result->get_error_message() );
			update_post_meta( $postId, Plugin::POST_META_PUSH_MESSAGE_ERROR, $result->get_error_message() );
			update_post_meta( $postId, Plugin::POST_META_PUSH_MESSAGE_ERROR_DATA, $result->get_error_data() );

			return false;
		}

		return true;
	}

}
