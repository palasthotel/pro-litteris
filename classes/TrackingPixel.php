<?php


namespace Palasthotel\ProLitteris;


use Palasthotel\ProLitteris\Model\Pixel;

/**
 * @property Plugin plugin
 */
class TrackingPixel extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_action( 'wp_head', [$this, 'head'] );
		add_filter( 'wp_footer', array( $this, 'footer' ) );
	}

	/**
	 * list of post types that are using pro litteris pixel
	 * @return array
	 */
	public function enabledPostTypes(){
		return apply_filters(Plugin::FILTER_POST_TYPES, array("post"));
	}

	/**
	 * check if a post type is activated for pixel
	 * @param string $postType
	 *
	 * @return bool
	 */
	public function isEnabled($postType){
		return in_array($postType, $this->enabledPostTypes());
	}

	public function head(){
		?>
		<!-- pro-litteris -->
		<meta name="referrer" content="no-referrer-when-downgrade">
		<?php
	}

	/**
	 * Add pixel to footer if exists
	 *
	 */
	public function footer() {

		if(!$this->isEnabled(get_post_type())){
			return;
		}

		if(!apply_filters(Plugin::FILTER_RENDER_PIXEL, true)){
		    return;
        }

		$pixel = $this->plugin->repository->getPostPixel(get_the_ID());

		if(!($pixel instanceof Pixel)){
			return;
		}

		echo '<img src="' . $pixel->toUrl() . '" height="1" width="1" border="0" class="pro-litteris-pixel" />';
	}


}
