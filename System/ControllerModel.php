<?php

namespace NE\System {

	/**
	 * @author Elchin Nagiyev
	 * 9/22/15 10:18 AM
	 */
	class ControllerModel {

		public $commandService;
		public $queryService;
		public $class;
		public $module;
		protected $tableName;
		protected $DI;
		protected $mode;
		protected $modeId;
		protected $config = [];

		public function __construct($mode = '') {
			$this->getModuleName();
			$this->getClassName();
			$this->setTableName();
			$this->mode = $mode;
			$this->setConfig();
			$this->setDirectoryInjectionVariable();
			$this->DI['mode'] = $this->mode;
		}

		private function setConfig() {
			$path = elch::$config['docroot'] . 'Modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . $this->module . 'Config.php';
			if (file_exists($path)) {
				$this->config = require $path;
			}
			if ($this->mode != '') {
				if (!in_array($this->mode, $this->config['modes'])) {
					trigger_error("The module '$this->module' is not have '" . $this->mode . "' mode. Check " . $this->module . "Config.php file.", E_USER_ERROR);
				}
				$path = elch::$config['docroot'] . 'Modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . $this->module . ucfirst($this->mode) . 'Config.php';
				if (file_exists($path)) {
					$modeConfig = require $path;
					$this->config = array_merge($this->config, $modeConfig);
				} else {
					trigger_error("The config file " . $this->module . "Config.php is not exist.", E_USER_ERROR);
				}
				$this->modeId = array_search($this->mode, $this->config['modes']);
			}
		}

		/**
		 * @return string
		 */
		public function getMode() {
			return $this->mode;
		}

		/**
		 * @return mixed
		 */
		public function getModeId() {
			return $this->modeId;
		}

		public function getConfig() {
			return $this->config;
		}

		public function saveConfig(array $newConfig) {
			$this->config = array_merge($this->config, $newConfig);
			$file = elch::$config['docroot'] . 'Modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . $this->module . $this->mode . 'Config.php';
			$fh = fopen($file, 'w') or die("can't open file " . $file);
			$date = time();
			$toSave = $this->config;
			if ($this->mode != '') {
				unset($toSave['modes']);
				$toSave['mode'] = $this->mode;
			}
			fwrite($fh, '<?php
/**
* Config file for the ' . $this->module . ' module.
* Generated automatically with "ElGroup" site builder.
*
* @generated ' . date("d M Y h:i:s", $date) . ';
* @by        ' . elch::$user['name'] . ');
* @siteurl   ' . elch::$grow['system']['domain'] . ';
*/

return ' . var_export($toSave, true) . ';');
		}

		private function setDirectoryInjectionVariable() {
			elch::$grow[$this->module]['config'] = &$this->config;
			$this->DI = &elch::$grow[$this->module];
		}

		private function setTableName() {
			if ($this->tableName == "") {
				/* $folders = preg_split('/(?#! splitCamelCase Rev:20140412)
				  # Split camelCase "words". Two global alternatives. Either g1of2:
				  (?<=[a-z])      # Position is after a lowercase,
				  (?=[A-Z])       # and before an uppercase letter.
				  | (?<=[A-Z])      # Or g2of2; Position is after uppercase,
				  (?=[A-Z][a-z])  # and before upper-then-lower case.
				  /x', $this->module); */
				$folders = preg_split(
					'/(^[^A-Z]+|[A-Z][^A-Z]+)/', $this->module, -1, /* no limit for replacement count */
					PREG_SPLIT_NO_EMPTY /* don't return empty elements */ | PREG_SPLIT_DELIM_CAPTURE /* don't strip anything from output array */
				);
				array_map('lcfirst', $folders);
				$this->tableName = strtolower(implode('_', $folders));
			}
			return $this->tableName;
		}

		private function getModuleName() {
			if ($this->module == "") {
				$classname = explode('\\', get_class($this));
				$this->module = $classname[2];
			}
		}

		private function getClassName() {
			if ($this->class == "") {
				$classname = get_class($this);

				if ($pos = strrpos($classname, '\\')) {
					$this->class = substr($classname, $pos + 1);
				}
				//$this->class = substr($this->class, 0, strcspn($this->class, 'ABCDEFGHJIJKLMNOPQRSTUVWXYZ'));
				$this->class = preg_split('/(?=[A-Z])/', $this->class);
				array_filter($this->class);
				array_pop($this->class);
				array_pop($this->class);
				$this->class = strtolower(implode('_', $this->class));
			}
			return $this->class;
		}

		public function getTableName($face = false, $table_name = false) {
			if ($table_name) {
				$table = $table_name;
			} else {
				$table = $this->tableName;
			}
			if ($face) {
				$table .= '_' . $face;
			}
			return $table;
		}

		public function printr($arr) {
			print '<div style="text - align:left;">';
			print '<pre style="text - align:left;">';
			print_r($arr);
			print '</pre>';
			print '</div>';
		}

		/**
		 * queryStr-də modula lazım olandan artıq elementlər varsa 404 erroru vermək üçün test
		 * @param int $key [default: false] queryStr arrayının istifadə olunan maksimum indeksidir. Əgər false olarsa queryStr-in heç bir elementinin olmamasını göstərir
		 * @return boolean
		 * Əgər queryStr arrayında $key indeksindən sonra da elementlər varsa, boş deyillərsə false qaytarır
		 * @since 12/9/11 1:46 PM
		 */
		public function is404($key = false) {
			if ($key === false) {
				$key = -1;
			}
			$l = count(elch::$queryStr);
			for ($i = $key + 1; $i <= $l; ++$i) {
				if (isset(elch::$queryStr[$i]) && elch::$queryStr[$i] != "") {
					return true;
				}
			}
		}

		public function mainAdmin() {
			switch (elch::$post['naviact']) {
				case 'edit':
					elch::$grow[$this->module]['theme'] = 'edit';
					$this->queryService->edit();
					break;
				case 'new':
					elch::$grow[$this->module]['theme'] = 'add';
					break;
				case 'delete':
					$this->commandService->delete();
					break;
				case 'active':
					$this->commandService->active();
					break;
				case 'unactive':
					$this->commandService->active(0);
					break;
			}
			switch (elch::$post['action']) {
				case 'add':
				case 'edit':
					$this->commandService->save();
					break;
			}
			$this->queryService->listNavi();
		}

		public function crawl(&$node, \NE\Modules\ElasticSearch\Controller\ElasticSearchControllerAdmin $elastic_search) {
			$index = '';
			foreach (elch::$site_langs as $lang) {
				if (isset($node['name'][$lang['id']])) {
					$node['title'][$lang['id']] = preg_replace('/\s+/', ' ', strip_tags($node['name'][$lang['id']]));
					$index .= trim(preg_replace('/\s+/', ' ', \NE\System\gain::to_translit($node['title'][$lang['id']] . ' ' . $node['desc'][$lang['id']], false))) . ' ';
				}
			}
			$return = $node + ['index' => $index, 'indexId' => $this->module . '-' . $node['id']];

			return [$return];
		}

	}

}

