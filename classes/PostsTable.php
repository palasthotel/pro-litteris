<?php


namespace Palasthotel\ProLitteris;


use Palasthotel\ProLitteris\Model\Pixel;

/**
 * @property Plugin plugin
 */
class PostsTable extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_filter( 'manage_posts_columns' , array($this, 'add_column') );
		add_action( 'manage_posts_custom_column' , array($this,'custom_columns'), 10, 2 );
	}

	public function add_column($columns){

		if(!$this->plugin->pixel->isEnabled(get_post_type())) return $columns;

		$newCols = array();
		$added = false;
		foreach ($columns as $key => $label){
			if( !$added && ($key == "comments" || $key == "date") ){
				$added = true;
				$newCols['pro-litteris'] = "ProLitteris";
			}
			$newCols[$key] = $label;
		}

		// if to any reason there is no comments or date column add it to the last position
		if($added == false){
			$newCols['pro-litteris'] = "ProLitteris";
		}

		return $newCols;
	}

	public function custom_columns($column, $post_id){
		if($column == 'pro-litteris'){

			$pixel = $this->plugin->repository->getPostPixel($post_id, true);

			if( $pixel instanceof \WP_Error ){
				$error = $pixel->get_error_message();
				echo "<span title='$error' style='cursor: help;'>🔴</span>";
				return;
			} else if($pixel instanceof Pixel){

				if(!$this->plugin->post->canBeReported($post_id)){
					echo "<span title='Kann nicht gemeldet werden, weil der Text unter ".Options::getMinCharCount()." Zeichen hat.' style='cursor: help;'>⚪️</span>";
					return;
				} else if($this->plugin->database->isMessageReported($pixel->uid)){
					echo "<span title='Inhalt wurde bei ProLitteris gemeldet' style='cursor: help;'>✅</span>";
					return;
				} else {
					echo "<span title='Inhalt ist bereit für die Meldung bei ProLitteris' style='cursor: help;'>🔶</span>";
					return;
				}
			}

			echo "<span title='Noch kein Zählpixel bei ProLitteris abgeholt' style='cursor: help;'>🔵</span>";
		}
	}
}
