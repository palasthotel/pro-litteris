<?php


namespace Palasthotel\ProLitteris;


use Palasthotel\ProLitteris\Model\Pixel;

class Migrate extends _Component {

	const PREFIX = "pro_litteris:";
	const FIELD_DOMAIN = "domain";
	const FIELD_UID = "uid";

	public function onCreate() {
		parent::onCreate();
		add_action( 'ph_migrate_register_field_handlers', [ $this, 'register' ] );
	}

	public function register() {
		ph_migrate_register_field_handler( 'ph_post_destination', self::PREFIX, [ $this, 'handler' ] );
	}

	public function handler( $post, $fields ) {

		$post_id = $post["ID"];

		$this->plugin->database->deleteForPost($post_id);

		if ( ! isset( $fields[ self::PREFIX . self::FIELD_DOMAIN ] ) || ! isset( $fields[ self::PREFIX . self::FIELD_UID ] ) ) {
			return;
		}

		$domain = $fields[ self::PREFIX . self::FIELD_DOMAIN ];
		$uid    = $fields[ self::PREFIX . self::FIELD_UID ];

		$this->plugin->database->add(Pixel::build($domain, $uid, $post_id));
	}

}
