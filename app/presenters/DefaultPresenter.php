<?php

namespace App\Presenters;

use Nette,
	App\Model;



class DefaultPresenter extends BasePresenter
{
	public function startup()
	{
		parent::startup();
                /////////////////////////////
	}


	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
