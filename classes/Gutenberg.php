<?php


namespace Palasthotel\ProLitteris;


class Gutenberg extends _Component {
	public function onCreate() {
		parent::onCreate();
		add_action( 'enqueue_block_editor_assets', function () {
			if(in_array(get_post_type(), $this->plugin->pixel->enabledPostTypes())){
				$this->plugin->assets->enqueueGutenberg();
			}
		});
	}
}
