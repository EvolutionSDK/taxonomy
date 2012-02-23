<?php

namespace Bundles\Taxonomy;
use Bundles\SQL\SQLBundle;
use Exception;
use e;

class Bundle extends SQLBundle {

	public function __callExtend($func, $args) {
		static $run = 0;
		if(($func == 'getTag' && !is_numeric($slug = $args[0])) || $run == 1) {
			list($category, $name) = explode(':', $slug);
			$result = e::$sql->query("SELECT * FROM `taxonomy.tag` WHERE `name` = '$name' AND `category` = '$category'")->row();
			$run = 1; if($result) return $this->getTag($result);
		}
		throw new e\AutoLoadException;
	}

}