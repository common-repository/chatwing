<?php namespace Chatwing\IntegrationPlugins\WordPress;

/**
 * @package Chatwing\IntegrationPlugins\Wordpress
 * @author chatwing
 */

class PluginBase
{
	/**
	 * @var DataModel
	 */
	protected $model = null;

	function __construct(DataModel $model) {
		$this->setModel($model);
	}

	protected function init(){}
	protected function registerHooks(){}
	protected function registerFilters(){}

	/**
	 * @return DataModel
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * @param DataModel $model
	 */
	public function setModel(DataModel $model)
	{
		$this->model = $model;
	}

	public function run() {
		$this->init();
		$this->registerHooks();
		$this->registerFilters();
	}
}