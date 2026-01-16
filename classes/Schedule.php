<?php


namespace Palasthotel\ProLitteris;


class Schedule extends _Component {

	public function onCreate() {
		parent::onCreate();
		add_action('admin_init', [$this, 'admin_init_schedule']);
		add_action(Plugin::SCHEDULE_REFILL_PIXEL_POOL, [$this, 'run']);
	}

	public function admin_init_schedule(){
		if(!wp_next_scheduled(Plugin::SCHEDULE_REFILL_PIXEL_POOL)){
			wp_schedule_event(time(), 'hourly', Plugin::SCHEDULE_REFILL_PIXEL_POOL);
		}
	}

	public function run(){
		$this->plugin->repository->refillPixelPool(Options::getPixelPoolSize());
		$this->plugin->repository->autoMessages();
	}
}
