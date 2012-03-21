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
				"model" => 'string',
				"model-id" => 'string',
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
		if(!$q) e::$sql->insert($tagTable, array(
			'model' => $map->__map('bundlename'),
			'model-id' => $map->id,
			'owner' => $model->id,
			'priority' => $priority
		));

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
	public function listHasTag(ListObj $list, $map = false) {

		/**
		 * Start by joining the tags table with the table of model
		 * @author Kelly Becker
		 */
		$list->join('LEFT', "\$tags $list->_table", "`$list->_table`.`id` = `\$tags $list->_table`.`owner`");

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
		 * I know we just got ourselves a model from a map but lets get the map back
		 * @author Kelly Becker
		 */
		try {
			if($map instanceof Model)
				$map = $map->__map();
			else throw new Exception("There was a unknown problem in List Has Tag", 500);
		}
		catch(Exception $e) {}

		/**
		 * If you are requesting an id with the map then break it up
		 * @author Kelly Becker
		 */
		if(strpos($map, ':') !== false) {
			list($model, $id) = explode(':', $map);
			$list->condition("`\$tags $list->_table`.`model` =", $model);
			$list->condition("`\$tags $list->_table`.`model-id` =", $id);
		}

		/**
		 * Id no id is requested then just list off the ones with tags to the model type
		 * @author Kelly Becker
		 */
		else $list->condition("`\$tags $list->_table`.`model` =", $arg);

		/**
		 * Set the order to be by tag priortiy
		 * @author Kelly Becker
		 */
		$list->order("`\$tags $list->_table`.`priority`", ASC);

		/**
		 * Return the list object
		 * @author Kelly Becker
		 */
		return $list;
	}

}