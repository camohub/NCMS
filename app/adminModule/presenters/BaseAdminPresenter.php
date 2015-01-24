<?php



namespace App\AdminModule\Presenters;



use	Nette,
	App,
	Nette\Diagnostics\Debugger;



class BaseAdminPresenter extends \App\Presenters\BasePresenter
{

	public function startup()
	{
		if(!$this->user->isLoggedIn())
		{
			$this->flashMessage('Pred vstupom do administrácie sa musíte prihlásiť.');
			$this->redirect(':Sign:in');
		}
		if(!$this->user->isAllowed('administration', 'view'))
		{
			throw new App\Exceptions\AccesDeniedException('Nemáte požadvané oprávnenie pre vstup do administrácie');
		}

		parent::startup();

		$this['breadcrumbs']->remove(0);
		$this['breadcrumbs']->add('Administrácia', ':Admin:Default:default');
	}

}
