<?php

namespace Bundles\SQL\Extensions;
use Bundles\SQL\ListObj;
use Bundles\SQL\Model;
use Exception;
use e;

class Taxonomy {

	public function _buildTable($tableName) {
		e::$sql->architect("\$tags $tableName", array(
			'fields' => array(
				"id" => '_suppress',
				"created_timestamp" => '_suppress',
				"owner" => 'number',false
				"updated_timestamp" => array(
					'Type' => 'timestamp',
					'Null' => 'YES',
					'Key' => '',
					'Default' => 'CURRENT_TIMESTAMP',
					'Extra' => 'on update CURRENT_TIMESTAMP'
				),
				"model" => array(
					'Type' => 'string',
					'Key' => 'PRI'
				),
				"model-id" => array(
					'Type' => 'string',
					'Key' => 'PRI'
				),
				"flags" => 'number'
			)
		));
	}

	public function _tableStructure($table, &$structure) {
		$structure['fields']['$tag-count'] = 'number';
	}

	public function modelTags(Model $model) {
		return e::taxonomy($model);
	}

	public function listHasTag(ListObj $list) {
		$args = func_get_args();
		array_shift($args);

		$list->join('LEFT', "\$tags $list->_table", "`$list->_table`.`id` = `\$tags $list->_table`.`owner`");

		foreach($args as $arg) {

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