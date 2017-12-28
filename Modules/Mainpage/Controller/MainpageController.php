<?php

namespace NE\Modules\Mainpage\Controller {

	use NE\Modules\Banner\Controller\BannerController;
	use NE\Modules\News\Controller\NewsController;
	use NE\Modules\Product\Controller\ProductController;
	use NE\System\ControllerModel;
	use NE\System\elch;
	use NE\System\Model;

	class MainpageController extends ControllerModel {

		function __construct() {
			parent::__construct();
			$this->tables = [
				'db'  => $this->getTableName(),
				'pin' => $this->getTableName('block')
			];
		}

		public function main() {
			$this->getContent();

			(new \NE\Modules\Cat\Controller\CatControllerAdmin())->get_cats_by_type(elch::$grow['Feedback'], ["feedback_subjects", "feedback_types"]);

			$bannerController = new BannerController();
			$bannerController->queryService->setBanners(['mainpage', 'mainpage_bottom', 'clients']);

			$projects = new ProductController('products');
			$projects->queryService->listCategories();

			elch::$grow['News']['lastnews'] = (new NewsController())->queryService->getNews(['db.`date`' => 'DESC'], [6], [1]);


			/*$projects = new ProductController('services');
			$projects->queryService->listProduct($projects->modeId, true);*/

			//$bannerController->queryService->setBanners(['mainpage_bottom'], 1, true);

			//(new BranchController())->queryService->listBranch();
			//(new ProductController())->queryService->listProduct();

			/*$products = new ProductController('partners');
			$products->queryService->listProduct($products->modeId, true);

			$projects = new ProductController('projects');
			$projects->queryService->listProduct($projects->modeId, false, false, [0, 6]);

			$projects = new ProductController('services');
			$projects->queryService->listProduct($projects->modeId, true);*/

			//(new \NE\Modules\Photoalbum\Controller\PhotoalbumController())->queryService->archive(84, true);
			return true;
		}

		private function getContent() {
			$model = new Model($this->tables['db']);
			$model->load(['fk_lang' => elch::$langId]);
			$this->DI = $model->toArray();
		}
	}

}

