<?php

namespace Bundles\Taxonomy;
use Bundles\SQL\SQLBundle;
use Bundles\SQL\callException;
use Exception;
use e;

/**
 * Taxonomy Bundle
 * @author Kelly Becker
 */
class Bundle extends SQLBundle {

	/**
	 * Extension for getTag to allow getting tags by name and category
	 * @author Kelly Becker
	 */
	public function __callExtend($func, $args) {

		/**
		 * Create a variable to determine if were on stage one or two
		 * @author Kelly Becker
		 */
		static $run = 0;

		/**
		 * If we are getting a tag, the argument passed is not numeric, and we havent been here before
		 * @author Kelly Becker
		 */
		if(($func == 'getTag' && is_string($slug = $args[0])) && $run < 1) {

			/**
			 * If we dont detect a category then use "default"
			 * @author Kelly Becker
			 */
			if(strpos($slug, ':') === false) {
				$category = 'default';
				$name = $slug;
			}

			/**
			 * Since we have detected a category seperate it out
			 * @author Kelly Becker
			 */
			else list($category, $name) = explode(':', $slug, 2);

			/**
			 * Run a query to find the requested tag
			 * @author Kelly Becker
			 */
			$result = e::$sql->query("SELECT * FROM `taxonomy.tag` WHERE `name` = '$name' AND `category` = '$category'")->row();

			/**
			 * Since we got this far we dont want to go through this again if we fail
			 */
			$run++;

			/**
			 * Get the model of the tag using the result of the query above
			 * @author Kelly Becker
			 */
			if($result) return $this->getTag($result);

			/**
			 * If no tag was found create one and return it
			 * @author Kelly Becker
			 */
			else {
				$tag = $this->newTag();
				$tag->category = $category;
				$tag->name = $name;
				$tag->save();
				$run = 0;
				return $tag;
			}
		}

		/**
		 * Since nothing was detected set run to zero so we can use the above script again
		 * @author Kelly Becker
		 */
		$run = 0;

		/**
		 * Throw an exception telling the call method to run normally
		 * @author Kelly Becker
		 */
		throw new callException;
	}

	public function route() {
		$tables = e::$sql->query("SHOW TABLES LIKE '\$tags%'")->all();
		foreach($tables as &$table)
			$table = array_shift($table);

		foreach($tables as $table) {
			$tags = e::$sql->query("SELECT * FROM `$table`")->all();
			foreach($tags as $tag) {
				
				if($tag['model'] !== 'taxonomy.tag')
					continue;

				$owner = $tag['owner'];
				$id = $tag['model-id'];
				$tag = $this->getTag($id);

				e::$sql->update($table, array(
					'string' => $tag->category.':'.$tag->name
				), "WHERE `owner` = '$owner'");

				$tag->__destruct();
				unset($tag);
			}
		}
		echo "Upgrade completed";
		e\Complete();
	}

}