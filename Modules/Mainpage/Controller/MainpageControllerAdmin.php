<?php

namespace NE\Modules\Mainpage\Controller {

	use NE\System\DPDO;
	use NE\System\elch;
	use NE\System\Model;

	class MainpageControllerAdmin extends \NE\System\ControllerModel
	{

		function __construct(){
			parent::__construct();
			$this->tables = [
				'db'  => $this->getTableName(),
			];
		}

		public function main(){
			switch (elch::$post['action']) {
				case 'save':
					$this->save();
					break;
				case 'pin':
					return $this->pin();
					break;
				case 'unpin':
					return $this->unpin();
					break;
			}
			$model = new Model($this->tables['db']);
			$model->load();
			while ($model->fetch()) {
				elch::$post[$this->module][$model->fk_lang] = $model->toArray();
			}
			return true;
		}

		private function save(){
			$post = &elch::$post[$this->module];
			$model = new Model($this->tables['db']);
			$model->load();
			foreach (elch::$site_langs as $k => $v) {
				$key = $model->opt(['fk_lang' => $v['id']], true);
				$model->loadArray($post[$v['id']], false, $key, true);
			}
			$model->save(false, true, 'IGNORE');
		}

	}

}

