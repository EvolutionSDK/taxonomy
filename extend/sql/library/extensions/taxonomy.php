<?php

namespace Bundles\SQL\Extensions;
use Bundles\SQL\ListObj;
use Bundles\SQL\Model;
use MapException;
use Exception;
use e;

class Taxonomy {

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
				"flags" => 'number'
			)
		));
	}

	public function _tableStructure($table, &$structure) {
		$structure['fields']['$tag_count'] = 'number';
	}

	public function modelAddTag(Model $model) {
		$args = func_get_args();
		array_shift($args);

		$tagTable = "\$tags ".$model->__getTable();

		if(count($args) > 0) if(is_array($args[0])) $args = $args[0];

		foreach($args as $map) {

			try {
				if($map instanceof Model);
				else $map = e::map($map);
			}

			catch(MapException $e) {
				$map = e::$taxonomy->getTag($map);
			}

			$q = e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();

			if(!$q) e::$sql->insert($tagTable, array(
				'model' => $map->__map('bundlename'),
				'model-id' => $map->id,
				'owner' => $model->id
			));

			$run = true;
		}

		if(!isset($run)) throw new Exception("No model Maps were passed");
		return true;
	}

	public function modelRemoveTag(Model $model) {
		$args = func_get_args();
		array_shift($args);

		$tagTable = "\$tags ".$model->__getTable();

		if(count($args) > 0) if(is_array($args[0])) $args = $args[0];

		foreach($args as $map) {
			try {
				if($map instanceof Model);
				else $map = e::map($map);
			}

			catch(MapException $e) {
				$map = e::$taxonomy->getTag($map);
			}

			$q = e::$sql->query("DELETE FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'");

			$run = true;
		}

		if(!isset($run)) throw new Exception("No model Maps were passed");
		return true;
	}

	public function modelHasTag(Model $model, $map = false) {
		if(!$map) throw new Exception("No model Map was passed");
		$tagTable = "\$tags ".$model->__getTable();

		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		catch(MapException $e) {
			$map = e::$taxonomy->getTag($map);
		}

		$q = e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();

		if(!$q) return false;
		else return true;
	}

	public function modelListTags(Model $model) {
		$tagTable = "\$tags ".$model->__getTable();

		return e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' ORDER BY `updated_timestamp` DESC")->all();
	}

	public function listHasTag(ListObj $list) {
		$args = func_get_args();
		array_shift($args);

		$list->join('LEFT', "\$tags $list->_table", "`$list->_table`.`id` = `\$tags $list->_table`.`owner`");

		foreach($args as $arg) {

			try {
				if($arg instanceof Model);
				else $arg = e::map($arg);
			}

			catch(MapException $e) {
				$arg = e::$taxonomy->getTag($arg);
			}

			if($arg instanceof Model)
				$arg = $arg->__map();
			else throw new Exception("There was a unknown problem in List Has Tag", 500);

			if(strpos($arg, ':') !== false) {
				list($model, $id) = explode(':', $arg);
				$list->condition("`\$tags $list->_table`.`model` =", $model);
				$list->condition("`\$tags $list->_table`.`model-id` =", $id);
			}
			else $list->condition("`\$tags $list->_table`.`model` =", $arg);
		}

		return $list;
	}

}