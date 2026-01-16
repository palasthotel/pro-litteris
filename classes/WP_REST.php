<?php


namespace Palasthotel\ProLitteris;


use Palasthotel\ProLitteris\Model\Pixel;
use WP_Error;

class WP_REST extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_action('rest_api_init', [$this, 'init']);
	}

	public function init(){

		$post_types = $this->plugin->pixel->enabledPostTypes();
		if ( empty( $post_types ) ) {
			return;
		}

		register_rest_field(
			$post_types,
			Plugin::REST_FIELD,
			[
				'get_callback'        => function ( $post ) {
					$postId   = $post["id"];

					$response = new \stdClass();

					$text = $this->plugin->post->getPostText($postId);

					$pixel = $this->plugin->repository->getPostPixel( $postId, true );
					if ( $pixel instanceof WP_Error ) {

						$error = $pixel->get_error_message( Plugin::ERROR_CODE_REQUEST );
						if ( empty( $error ) ) {
							$response->error = $pixel->get_error_message();
						} else {
							$response->error = $pixel->get_error_message();
						}

						return $response;
					}

					if ( ! ( $pixel instanceof Pixel ) ) {
						$response->info = "Konnte keinen Pixel für diesen Inhalt zuweisen.";
						return $response;
					}

					$response->pixel = [
						"uid" => $pixel->uid,
						"domain" => $pixel->domain,
						"url" => $pixel->toUrl(),
					];

					$response->message = $this->plugin->database->getMessage($pixel->uid);

					if(false === $response->message){
						$postMessage = $this->plugin->post->getPostMessage($postId);
						$response->messageDraft = new \stdClass();
						if( $postMessage instanceof WP_Error ){
							$response->messageDraft->error = $postMessage->get_error_message();
						} else {
							$response->messageDraft->pixelUid = $postMessage["pixelUid"];
							$response->messageDraft->title = $postMessage["title"];
							$response->messageDraft->participants = $postMessage["participants"];
							$response->messageDraft->plaintext = $text;
						}
					}

					$pushError = get_post_meta($postId, Plugin::POST_META_PUSH_MESSAGE_ERROR, true);
					if(false !== $pushError && !empty($pushError)){
						$response->pushError = $pushError;
						$response->pushErrorData = get_post_meta($postId, Plugin::POST_META_PUSH_MESSAGE_ERROR_DATA, true);
					}

					return $response;
				},
				'update_callback'     => function ( $value, $post ) {

					if ( ! is_array( $value ) ) {
						return;
					} // there is nothing to change for us

					if(isset($value["pushMessage"])){
						$this->plugin->repository->reportPost($post->ID, true);
					}
				},
				'permission_callback' => function ( $request ) {
					return current_user_can( 'edit_post', $request["id"] );
				},
			]
		);

		register_rest_field(
			"attachment",
			Plugin::REST_FIELD_ATTACHMENT_AUTHOR,
			[
				'get_callback'        => function ( $post ) {
					return $this->plugin->media->getAuthor( $post["id"] );
				},
				'permission_callback' => function ( $request ) {
					return current_user_can( 'edit_post', $request["id"] );
				},
			]
		);
	}

}
