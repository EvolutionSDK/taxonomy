<?php

namespace Bundles\SQL\Extensions;
use Bundles\SQL\ListObj;
use Bundles\SQL\Model;
use MapException;
use Exception;
use e;

/**
 * Taxonomy SQL Extension
 * @author Kelly Becker
 * @author Nate Ferrero
 */
class Taxonomy {

	/**
	 * Event to build the connections table on sql build
	 * @author Kelly Becker
	 */
	public function _buildTable($tableName) {
		e::$sql->architect("\$tags $tableName", array(
			'fields' => array(
				"id" => '_suppress',
				"created_timestamp" => '_suppress',
				"owner" => 'number',
				"updated_timestamp" => array(
					'Type' => 'timestamp',
					'Null' => 'YES',
					'Key' => '',
					'Default' => 'CURRENT_TIMESTAMP',
					'Extra' => 'on update CURRENT_TIMESTAMP'
				),
				"+model" => 'string',
				"+model-id" => 'number',
				"+string" => 'string',
				"flags" => 'number',
				"priority" => 'number'
			)
		));
	}

	/**
	 * Add an additional field to the table
	 * @author Kelly Becker
	 */
	public function _tableStructure($table, &$structure) {
		$structure['fields']['$tag_count'] = 'number';
	}

	/**
	 * Add a tag by model map or tag name
	 * @author Kelly Becker
	 */
	public function modelAddTag(Model $model, $map = false, $priority = 0) {

		/**
		 * Generate the name of the table were gonna use
		 * @author Kelly Becker
		 */
		$tagTable = "\$tags ".$model->__getTable();

		/**
		 * If a model instace was passed do nothing, if a map was passed convert it to a model
		 * @author Kelly Becker
		 */
		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		/**
		 * If the map that was passed was not a valid map it must be a tag instead. Convert it to a tag
		 * @author Kelly Becker
		 */
		catch(MapException $e) {
			$realtag = true;
			$map = e::$taxonomy->getTag($map);
		}

		/**
		 * See if a tag already exists
		 * @author Kelly Becker
		 */
		$q = e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();

		/**
		 * If the tag does not exist yet create it on the table
		 * @author Kelly Becker
		 */
		$insert = array(
			'model' => $map->__map('bundlename'),
			'model-id' => $map->id,
			'owner' => $model->id,
			'priority' => $priority
		);

		if(isset($realtag))
			$insert['string'] = $map->category.':'.$map->name;

		if(!$q) e::$sql->insert($tagTable, $insert);

		return true;
	}

	/**
	 * Remove a tag by model or tag name
	 * @author Kelly Becker
	 */
	public function modelRemoveTag(Model $model, $map = false) {

		/**
		 * Generate the name of the table were gonna use
		 * @author Kelly Becker
		 */
		$tagTable = "\$tags ".$model->__getTable();

		/**
		 * If a model instace was passed do nothing, if a map was passed convert it to a model
		 * @author Kelly Becker
		 */
		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		/**
		 * If the map that was passed was not a valid map it must be a tag instead. Convert it to a tag
		 * @author Kelly Becker
		 */
		catch(MapException $e) {
			$map = e::$taxonomy->getTag($map);
		}

		/**
		 * Delete the tag connection from the tag table
		 * @author Kelly Becker
		 */
		$q = e::$sql->query("DELETE FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();

		return true;
	}

	/**
	 * Check if a tag exists on the model
	 * @author Kelly Becker
	 */
	public function modelHasTag(Model $model, $map = false) {

		/**
		 * Check to ensure that a map was passed
		 * @author Kelly Becker
		 */
		if(!$map) throw new Exception("No model Map was passed");

		/**
		 * Generate the name of the table were gonna use
		 * @author Kelly Becker
		 */
		$tagTable = "\$tags ".$model->__getTable();

		/**
		 * If a model instace was passed do nothing, if a map was passed convert it to a model
		 * @author Kelly Becker
		 */
		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		/**
		 * If the map that was passed was not a valid map it must be a tag instead. Convert it to a tag
		 * @author Kelly Becker
		 */
		catch(MapException $e) {
			$map = e::$taxonomy->getTag($map);
		}

		/**
		 * Check if we get any results
		 * @author Kelly Becker
		 */
		$q = e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();

		/**
		 * If there was a result return true, else return false
		 * @author Kelly Becker
		 */
		if(!$q) return false;
		else return true;
	}

	/**
	 * List all the connected tags on the model
	 * @author Kelly Becker
	 */
	public function modelListTags(Model $model) {

		/**
		 * Generate the name of the table were gonna use
		 * @author Kelly Becker
		 */
		$tagTable = "\$tags ".$model->__getTable();

		/**
		 * Get and return all the tags on the model
		 * @author Kelly Becker
		 */
		return e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' ORDER BY `updated_timestamp` DESC")->all();
	}

	/**
	 * Prioritize a tag
	 * @author Kelly Becker
	 */
	public function modelPrioritizeTag(Model $model, $map = false, $priority = 0, $up_down = false) {

		/**
		 * Check to ensure that a map was passed
		 * @author Kelly Becker
		 */
		if(!$map) throw new Exception("No model Map was passed");

		/**
		 * Generate the name of the table were gonna use
		 * @author Kelly Becker
		 */
		$tagTable = "\$tags ".$model->__getTable();


		/**
		 * If a model instace was passed do nothing, if a map was passed convert it to a model
		 * @author Kelly Becker
		 */
		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		/**
		 * If the map that was passed was not a valid map it must be a tag instead. Convert it to a tag
		 * @author Kelly Becker
		 */
		catch(MapException $e) {
			$map = e::$taxonomy->getTag($map);
		}

		/**
		 * Are we are changin a priority by one
		 * @author Kelly Becker
		 */
		if($up_down) {
			$q = e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();
			if($priority) $priority = ((float) $q['priority']) + 1;
			else $priority = ((float) $q['priority']) - 1;
		}

		/**
		 * Set Priority on the tag
		 * @author Kelly Becker
		 */
		$q = e::$sql->query("UPDATE `$tagTable` SET `priority` = '$priority' WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();

		/**
		 * If we succeeded return true, else return false
		 * @author Kelly Becker
		 */
		if(!$q) return false;
		else return true;
	}

	/**
	 * Filtering function for lists
	 * @author Kelly Becker
	 */
	public function listHasTag(ListObj $list, $map = false, $or = false) {
		if(!isset($list->__taxonomy_cache)) $list->__taxonomy_cache = array();

		/**
		 * If a model instace was passed do nothing, if a map was passed convert it to a model
		 * @author Kelly Becker
		 */
		try {
			if($map instanceof Model);
			else $map = e::map($map, true);
		}

		/**
		 * If the map that was passed was not a valid map it must be a tag instead. Convert it to a tag
		 * @author Kelly Becker
		 */
		catch(MapException $e) {
			/**
			 * If we dont detect a category then use "default"
			 * @author Kelly Becker
			 */
			if(strpos($map, ':') === false)
				$map = 'default:'.$map;

			$realtag = true;
		}

		/**
		 * If we have a model get the map
		 * @author Kelly Becker
		 */
		if($map instanceof Model) $map = $map->__map();

		/**
		 * Start working with the list
		 */
		if(isset($realtag))
			$list->__taxonomy_cache[($or ? 'O' : 'A').':taxonomy.tag'][] = $map;
		else {
			list($model, $id) = explode(':', $map, 2);
			$list->__taxonomy_cache[($or ? 'O' : 'A').':'.$model][] = $id;
		}

		/**
		 * Return the list object
		 * @author Kelly Becker
		 */
		return $list;
	}

	/**
	 * Run the tag filtration
	 * @author Kelly Becker
	 */
	public function list_on_run_query(ListObj $list) {
		if(empty($list->__taxonomy_cache)) return false;
		
		$tax_cache = $list->__taxonomy_cache;

		foreach($tax_cache as $tag => $ids) {
			$flag = substr($tag, 0, 1);
			$tag = substr($tag, 2);

			$query = "`model` = '$tag'";
			if($tag !== 'taxonomy.tag' && $flag == 'O') {
				foreach($ids as $id) {
					if(!empty($query))
						$query .= ' || ';

					$query .= "`model-id` = '$id'";
				}
			}

			else if($tag !== 'taxonomy.tag' && $flag == 'A') {
				foreach($ids as $id) {
					if(!empty($query))
						$query .= ' && ';

					$query .= "`model-id` = '$id'";
				}
			}

			else if($tag === 'taxonomy.tag' && $flag == 'O') {
				foreach($ids as $string) {
					if(empty($string))
						break;
					if(!empty($query))
						$query .= ' || ';

					$query .= "`string` = '$string'";
				}
			}

			else if($tag === 'taxonomy.tag' && $flag == 'A') {
				foreach($ids as $string) {
					if(empty($string))
						break;
					if(!empty($query))
						$query .= ' && ';

					$query .= "`string` = '$string'";
				}
			}

			if(empty($query)) continue;

			$query = "`id` IN (SELECT `owner` FROM `\$tags $list->_table` WHERE $query)";
			$list->manual_condition($query);
		}

		/**
		 * Destroy the cache
		 */
		$list->__taxonomy_cache = array();

		/**
		 * Left join the tags table
		 */
		$list->join('LEFT', "\$tags $list->_table", "`$list->_table`.`id` = `\$tags $list->_table`.`owner`");

		/**
		 * Group By ID
		 */
		$list->group_by("$list->_table`.`id");

		/**
		 * Set the order to be by tag priortiy
		 * @author Kelly Becker
		 */
		$list->order("`\$tags $list->_table`.`priority`", 'ASC');
	}

}