<?php

namespace NE\System {

	/**
	 * @author Elchin Nagiyev <elchin at nagiyev.net>
	 */
	class ModuleAdapter {

		protected $module;
		protected $controllerSufix = '';

		public function parse() {
			if (elch::$queryStr[0] && array_key_exists(elch::$queryStr[0], elch::$config['modules'])) {
				$this->module = elch::$queryStr[0];
				unset(elch::$queryStr[0]);
				elch::$queryStr = array_values(elch::$queryStr);
			}
			if (empty($this->module)) {
				if (empty(elch::$queryStr)) {
					$this->module = elch::$config['default_module'];
				} else {
					$this->module = "NotFound";
				}
			}
			$this->module = ucfirst($this->module);
			elch::$module = $this->module;
			elch::$moduleName = $this->module;
		}

		public function getModule() {
			return $this->module;
		}

		public function setModule($module) {
			$this->module = $module;
			elch::$module = $this->module;
		}

		public function setControllerSufix($controllerSufix) {
			$this->controllerSufix = $controllerSufix;
		}

		public function runModule($module = false) {
			if ($module !== false) {
				$this->module = $module;
				elch::$module = $this->module;
			}
			if (!array_key_exists($this->module, elch::$config['modules'])) {
				trigger_error("The module '$this->module' is not exist", E_USER_ERROR);
			}
			if (!empty(elch::$config['modules'][$this->module]['extends'])) {
				elch::$moduleName = $this->module;
				$m = elch::$config['modules'][$this->module]['extends'];
				$mode = elch::$config['modules'][$this->module]['mode'];
				elch::$module = $this->module = $m;
				require_once elch::$config['docroot'] . 'Modules/' . $m . '/Controller/' . $m . 'Controller' . $this->controllerSufix . '.php';
				$obj_name = 'NE\\Modules\\' . $m . '\\Controller\\' . $m . 'Controller' . $this->controllerSufix;
				$obj = new $obj_name($mode);
			} else {
				require_once elch::$config['docroot'] . 'Modules/' . $this->module . '/Controller/' . $this->module . 'Controller' . $this->controllerSufix . '.php';
				$obj_name = 'NE\\Modules\\' . $this->module . '\\Controller\\' . $this->module . 'Controller' . $this->controllerSufix;
				$obj = new $obj_name();

			}
			$found = $obj->main();
			if (!$module && !$found) {
				$this->runModule('NotFound');
			}
			return $found;
		}

	}

}
