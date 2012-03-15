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
				"flags" => 'number',
				"priority" => 'number'
			)
		));
	}

	public function _tableStructure($table, &$structure) {
		$structure['fields']['$tag_count'] = 'number';
	}

	public function modelAddTag(Model $model, $map = false, $priority = 0) {
		$tagTable = "\$tags ".$model->__getTable();

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
			'owner' => $model->id,
			'priority' => $priority
		));

		return true;
	}

	public function modelRemoveTag(Model $model, $map = false) {
		$tagTable = "\$tags ".$model->__getTable();

		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		catch(MapException $e) {
			$map = e::$taxonomy->getTag($map);
		}

		$q = e::$sql->query("DELETE FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'");

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

	public function modelPrioritizeTag(Model $model, $map = false, $priority = 0, $up_down = false) {
		if(!$map) throw new Exception("No model Map was passed");
		$tagTable = "\$tags ".$model->__getTable();

		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		catch(MapException $e) {
			$map = e::$taxonomy->getTag($map);
		}


		if($up_down) {
			$q = e::$sql->query("SELECT * FROM `$tagTable` WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();
			if($priority) $priority = ((float) $q['priority']) + 1;
			else $priority = ((float) $q['priority']) - 1;
		}

		$q = e::$sql->query("UPDATE `$tagTable` SET `priority` = '$priority' WHERE `owner` = '$model->id' AND `model` = '".$map->__map('bundlename')."' AND `model-id` = '$map->id'")->row();

		if(!$q) return false;
		else return true;
	}

	public function listHasTag(ListObj $list, $map = false) {

		$list->join('LEFT', "\$tags $list->_table", "`$list->_table`.`id` = `\$tags $list->_table`.`owner`");

		try {
			if($map instanceof Model);
			else $map = e::map($map);
		}

		catch(MapException $e) {
			$map = e::$taxonomy->getTag($map);
		}

		try {
			if($map instanceof Model)
				$map = $map->__map();
			else throw new Exception("There was a unknown problem in List Has Tag", 500);
		}
		catch(Exception $e) {}

		if(strpos($map, ':') !== false) {
			list($model, $id) = explode(':', $map);
			$list->condition("`\$tags $list->_table`.`model` =", $model);
			$list->condition("`\$tags $list->_table`.`model-id` =", $id);
		}
		else $list->condition("`\$tags $list->_table`.`model` =", $arg);

		$list->order("`\$tags $list->_table`.`priority`", ASC);

		return $list;
	}

}