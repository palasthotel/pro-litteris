<?php


namespace Palasthotel\ProLitteris;


use WP_Error;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class CLI {

	/**
	 * Refill pixel pool
	 *
	 * ## OPTIONS
	 *
	 * [--to=<size>]
	 * : refill to pool size
	 * ---
	 * default: -1
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp pro-litteris refillPool post --to=10
	 *
	 * @when after_wp_load
	 */
	public function refillPool($args, $assoc_args){
		$size = isset($assoc_args["to"]) ? intval($assoc_args["to"]) : -1;
		$size = $size < 0 ? Options::getPixelPoolSize() : $size;

		$plugin = Plugin::instance();

		if(!$plugin->isEnabled()){
			\WP_CLI::error("ProLitteris is not enabled in config.");
			exit;
		}
		if(!$plugin->hasConfig()){
			\WP_CLI::error("ProLitteris is missing config.");
			exit;
		}

		$result = $plugin->repository->refillPixelPool($size);

		if($result instanceof WP_Error){
			\WP_CLI::error($result);
			exit;
		}

		if($result < 1){
			\WP_CLI::success( "Pixel pool seems to be full already!" );
			exit;
		}

		\WP_CLI::success( "Added $result pixel to pixel pool!" );
	}

	/**
	 * Report contents
	 *
	 * ## OPTIONS
	 *
	 * <year>
	 * : Which year to report
	 *
	 * ## EXAMPLES
	 *
	 *     wp pro-litteris reportContents 2020
	 *
	 * @when after_wp_load
	 */
	public function reportContents($args, $assoc_args){

		list( $year ) = $args;

		$year = intval($year);

		if($year <= 1970){
			\WP_CLI::error("Please use a year after 1970");
			return;
		}

		\WP_CLI::log( "🕐 -> Report year $year" );
		$plugin = Plugin::instance();
		$postIds = $plugin->database->getPostIdsReadyForMessage($year);
		$progress = \WP_CLI\Utils\make_progress_bar( 'Reporting', count($postIds) );
		$success =  0;
		foreach ($postIds as $postId){
			if(!WP_DEBUG){
				$plugin->repository->reportPost($postId);
				// wait for the report to really have finished (I guess on their side).
				// else we run into error":{"code":100,"message":"Technical error (too many requests).
				usleep(50 * 1000);
			}
			$success++;
			$progress->tick();
		}
		$error = $success - count($postIds);
		\WP_CLI::success( "Reporting year $year done! $success successfull, $error errored." );

	}

}


\WP_CLI::add_command(
	"pro-litteris",
	__NAMESPACE__."\CLI",
	array(
		'shortdesc' => 'ProLitteris commands.',
	)
);
