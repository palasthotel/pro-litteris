<?php


namespace Palasthotel\ProLitteris;


class Assets extends _Component {

	function enqueueGutenberg(){
		$info = include $this->plugin->path . "/dist/gutenberg.asset.php";
		wp_enqueue_script(
			Plugin::HANDLE_GUTENBERG_JS,
			$this->plugin->url . "/dist/gutenberg.js",
			$info["dependencies"],
			$info["version"]
		);

		if(file_exists($this->plugin->path."/dist/gutenberg.css")){
			wp_enqueue_style(
				Plugin::HANDLE_GUTENBERG_CSS,
				$this->plugin->url."/dist/gutenberg.css",
				[],
				filemtime($this->plugin->path."/dist/gutenberg.css")
			);
		}
	}

}
