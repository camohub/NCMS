<?php
namespace App\AdminModule\BlogModule\Presenters;

use	Nette,
	App,
	Nette\Utils\Finder,
	Nette\Diagnostics\Debugger;

class GaleryPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{


	public function renderDefault($dir = 'blog')
	{
		$allow = array('products','blog','forum');
		if(!in_array($dir, $allow))
		{
			throw new App\Exceptions\InvalidArgumentException('Parameter ' . $dir . ' nieje v platnom rozsahu.');
		}

		$this->template->dir = $dir;
		$this->template->url = $this->context->parameters['documentRoot'].'/www/images/'.$dir.'/thumbnails';
		// Nex path is not the same as url!!! url == /NCMS/...    in() param == C:\Apache24....
		$this->template->files = Finder::find('*')->in($this->context->parameters['wwwDir'].'/images/'.$dir.'/thumbnails');

	}


}
