<?php


namespace Palasthotel\ProLitteris;

use Palasthotel\ProLitteris\Model\Message;
use Palasthotel\ProLitteris\Model\Pixel;

/**
 * @property string table
 * @property string tableResponses
 * @property string $tableMessages
 */
class Database extends Components\Database {

	public function init() {
		$this->table          = $this->wpdb->prefix . "pro_litteris_pixel_pool";
		$this->tableMessages  = $this->wpdb->prefix . "pro_litteris_messages";
		$this->tableResponses = $this->wpdb->prefix . "pro_litteris_api_responses";
	}

	/**
	 * @return int
	 */
	public function countAvailablePixels() {
		return intval($this->wpdb->get_var("SELECT count(uid) FROM $this->table WHERE post_id IS NULL"));
	}

	/**
	 *
	 * @param Pixel $pixel
	 *
	 * @return bool|int
	 */
	public function add(Pixel $pixel){
		return $this->wpdb->insert(
			$this->table,
			[
				"uid" => $pixel->uid,
				"uid_domain" => $pixel->domain,
				"post_id" => $pixel->post_id,
			],
			[ "%s"]
		);
	}

	/**
	 *
	 * @param int|string $post_id
	 *
	 * @return Pixel|null
	 */
	public function getPixel( $post_id ){
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare("SELECT uid, uid_domain, post_id from $this->table WHERE post_id = %d", $post_id)
		);
		if(!is_object($row) || !isset($row->uid)) return null;

		return $this->rowToPixel($row);
	}

	/**
	 * @param $post_id
	 *
	 * @return bool|int
	 */
	public function assignPixel($post_id){
		return $this->wpdb->query($this->wpdb->prepare("UPDATE $this->table SET post_id = %d WHERE uid IN (
			SELECT * FROM (SELECT uid FROM $this->table WHERE post_id IS NULL LIMIT 1) as tmp
		)", $post_id));
	}

	/**
	 * @param mixed $row
	 *
	 * @return Model\Pixel
	 */
	private function rowToPixel($row){
		return Model\Pixel::build($row->uid_domain, $row->uid, isset($row->post_id) ? $row->post_id:null);
	}

	/**
	 * @param int|string $post_id
	 *
	 * @return bool|int
	 */
	public function deleteForPost($post_id){
		return $this->wpdb->delete(
			$this->table,
			[
				"post_id" => $post_id,
			]
		);
	}

	/**
	 * @param Message $message
	 *
	 * @param string|int|null $user_id
	 * @param string|null $response
	 *
	 * @return bool|int
	 */
	public function saveMessage(Message $message, $user_id = null, string $response = null){
		return $this->wpdb->replace(
			$this->tableMessages,
			[
				"pixel_uid" => $message->pixelUid,
				"title" => $message->title,
				"plaintext" => $message->plaintext,
				"participants" => json_encode($message->participants),
				"response" => $response,
				"reported" => $message->reported,
				"reported_by" => $user_id,
			]
		);
	}

	/**
	 * @param $pixelUid
	 *
	 * @return bool
	 */
	public function isMessageReported($pixelUid){
		return intval($this->wpdb->get_var(
			$this->wpdb->prepare("SELECT count(pixel_uid) FROM $this->tableMessages WHERE pixel_uid = %s AND reported IS NOT null", $pixelUid)
		)) > 0;
	}

	/**
	 * @param string $pixelUid
	 *
	 * @return false|Message
	 */
	public function getMessage($pixelUid){
		$row = $this->wpdb->get_row(
			$this->wpdb->prepare("SELECT * FROM $this->tableMessages WHERE pixel_uid = %s", $pixelUid)
		);
		if(!isset($row->pixel_uid)){
			return false;
		}
		$message = new Message($row->pixel_uid);
		$message->title = $row->title;
		$message->plaintext = $row->plaintext;
		$message->reported = $row->reported;
		$message->participants = json_decode($row->participants);
		$message->response = null != $row->response ? json_decode($row->response, true) : null;

		return $message;
	}

	/**
	 *
	 * @param int $year
	 *
	 * @return array
	 */
	public function getPostIdsReadyForMessage($year = -1){
		$yearCond = "";
		if($year > 0){
			$yearCond = " AND YEAR(post_date) = ".intval($year);
		}
		return $this->wpdb->get_col( 'SELECT p.ID from '.$this->wpdb->posts.' as p
			LEFT JOIN '.$this->wpdb->usermeta.' as u ON ( p.post_author = u.user_id AND u.meta_key = "'.Plugin::USER_META_PRO_LITTERIS_ID.'" )
			WHERE
			p.ID IN (
				SELECT post_id from '.$this->table.' as pool WHERE uid NOT IN (
					SELECT pixel_uid FROM '.$this->tableMessages.'
				) AND post_id IS NOT NULL
			)
			AND
			p.ID NOT IN (
				SELECT post_id FROM '.$this->wpdb->postmeta.' WHERE meta_key = "'.Plugin::POST_META_PUSH_MESSAGE_ERROR.'"
			)
			AND p.post_status IN ( "publish", "private" )
			AND u.meta_value IS NOT NULL '.$yearCond);
	}

	/**
	 * @param string $response
	 *
	 * @param string $message
	 *
	 * @param null $pixelUID
	 *
	 * @return bool|int
	 */
	public function addAPIResponse(string $response, string $message = "", $pixelUID = null){
		return $this->wpdb->insert(
			$this->tableResponses,
			[
				"response" => $response,
				"message" => $message,
				"requested" => time(),
				"pixel_uid" => $pixelUID,
			]
		);
	}

	/**
	 * create tables if they do not exist
	 */
	function createTables() {
		parent::createTables();

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->table
			(
			uid varchar(100) not null,
			uid_domain varchar(100) not null,
			post_id bigint(20) default null,

			primary key (uid),
			key (uid_domain),
			key (post_id),
			unique key post_pixel ( uid, post_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableMessages
			(
			pixel_uid varchar(100) not null,
			title varchar(190) NOT NULL,
			plaintext TEXT NOT NULL,
			participants TEXT NOT NULL,
			response TEXT DEFAULT NULL,
			reported bigint(20) DEFAULT NULL,
			reported_by bigint(20) DEFAULT NULL,

			primary key (pixel_uid),
			key (title),
			key (reported),
			key (reported_by)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );

		\dbDelta( "CREATE TABLE IF NOT EXISTS $this->tableResponses
			(
			id bigint(20) unsigned auto_increment,
			response TEXT NOT NULL,
			requested bigint(20) NOT NULL,
			message TEXT NOT NULL,
			pixel_uid varchar(100) DEFAULT NULL,

			primary key (id),
			key (requested),
			key (pixel_uid)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" );
	}
}
