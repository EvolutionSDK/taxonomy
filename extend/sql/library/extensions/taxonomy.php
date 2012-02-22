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
				)
			)
		));
	}

	public function _tableStructure($table, &$structure) {
		$structure['fields']['$tag-count'] = 'number';
	}

	public function modelTags(Model $model) {
		return e::taxonomy($model);
	}

	public function listHasTag(ListObj $list, $model) {
		$args = func_get_args();
		array_shift($args);

		foreach($args as $arg)
			$list->condition('model', $arg);

		return $list;
	}

}

$list->_->taxonomy->hasTag('members.member', 'momentum.project');