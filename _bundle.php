<?php

namespace Bundles\Taxonomy;
use Bundles\SQL\SQLBundle;
use Exception;
use e;

class Bundle extends SQLBundle {

	public function __callExtend($func, $args) {
		static $run = 0;

		if(($func == 'getTag' && !is_numeric($slug = $args[0])) && $run < 1) {
			if(strpos($slug, ':') === false) {
				$category = 'default';
				$name = $slug;
			}
			else list($category, $name) = explode(':', $slug, 2);
			$result = e::$sql->query("SELECT * FROM `taxonomy.tag` WHERE `name` = '$name' AND `category` = '$category'")->row();
			$run++;

			if($result) return $this->getTag($result);
			else {
				$tag = $this->newTag();
				$tag->category = $category;
				$tag->name = $name;
				$tag->save();
				return $tag;
			}
		}
		$run = 0;
		throw new e\AutoLoadException;
	}

}