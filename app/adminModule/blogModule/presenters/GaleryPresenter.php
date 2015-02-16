<?php
namespace App\AdminModule\BlogModule\Presenters;

use	Nette,
	App,
	App\Model\Images,
	Nette\Utils\Finder,
	Nette\Diagnostics\Debugger;

class GaleryPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{

	/** @var  App\Model\Images */
	protected $imagesModel;



	public function startup()
	{
		parent::startup();
		$this->imagesModel = new Images($this->database);
	}



	public function renderDefault()
	{
		$images = $this->imagesModel->findBy(array('module_id' => 1), 'admin')->order('id DESC');

		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 15;
		$paginator->itemCount = count($images);

		$this->template->images = $images->limit($paginator->itemsPerPage, $paginator->offset);
		$this->template->page = $paginator->page;


	}


}
