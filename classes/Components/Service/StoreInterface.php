<?php


namespace Palasthotel\ProLitteris\Components\Service;

/**
 * Interface StoreInterface
 * @version 0.1.1
 */
interface StoreInterface {
	public function set($id, $value);
	public function get($id);
	public function delete($id);
}
