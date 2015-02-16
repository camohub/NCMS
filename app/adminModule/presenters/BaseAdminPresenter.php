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
			$this->redirect(':Sign:in', $this->storeRequest());
		}
		if(!$this->user->isAllowed('administration', 'view'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte požadované oprávnenie pre vstup do administrácie.');
		}

		parent::startup();

		$this['breadcrumbs']->remove(0);  // parent BasePresenter adds Default:default in startup
		$this['breadcrumbs']->add('Administrácia', ':Admin:Default:default');
	}


}
