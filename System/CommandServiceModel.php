<?php

namespace NE\System {

	/**
	 * Description of commandServiceModel
	 *
	 * @author Elchin Nagiyev <elchin at nagiyev.net>
	 * 9/21/15 3:46 PM
	 */
	class CommandServiceModel {

		protected $tables = [];
		public $class;
		public $module;
		public $DI;
		protected $config = [];

		function __construct(array $tables, array $config = []) {
			$this->tables = $tables;
			$this->config = $config;
			$this->getClassName();
			$this->getModuleName();
			$this->DI = &elch::$grow[$this->module];
		}

		public function getConfig() {
			return $this->config;
		}

		private function getClassName() {
			if ($this->class == "") {
				$classname = explode('\\', get_class($this));
				$this->class = $classname[2];
			}
		}

		private function getModuleName() {
			if ($this->module == "") {
				$classname = explode('\\', get_class($this));
				$this->module = $classname[2];
			}
		}

		public function triggerMessage($msg, $result = false) {
			if (elch::$post['ajax']) {
				elch::$grow['skin'] = 'empty.tpl';
				print json_encode(['result' => $result, 'message' => $msg]);
			}
			elch::$grow[$this->module]['message'] = $msg;
			return true;
		}

		public function save($modeId = NULL) {
			$post = &elch::$post[$this->module];

			$module = new Model($this->tables['db']);
			$primary = $module->getPrimary();

			$id = (int)$post[$primary[0]];
			if ($id) {
				$module->load([$primary[0] => $id]);
			} else {
				$module->setDefaultData();
			}
			$module->loadArray($post, false, 0, true);
			$module->save();
		}

		public function active($status = 1) {
			$id = elch::$post['navi'];
			$module = new Model($this->tables['db']);
			$primary = $module->getPrimary();
			$module->load([$primary[0] => $id]);
			$module->set('status', $status);
			$module->save();
		}

		public function dublicate($id, Model $model) {
			$primary = $model->getPrimary();
			$model->load([$primary[0] => $id]);
			$newModel = $model->dublicate();
			//gain::printr($newModel);
			$newModel->save();
		}

	}

}
