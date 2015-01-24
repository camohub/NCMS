<?php
namespace App\AdminModule\BlogModule\Presenters;

use	Nette,
	App,
	Nette\Diagnostics\Debugger;

class DefaultPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{


	public function renderDefault()
	{
		$this['breadcrumbs']->add('blog', ':Admin:Blog:Default:default');

	}


}
