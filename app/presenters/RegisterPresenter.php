<?php

namespace App\Presenters;

use	Nette,
	App,
	App\Exceptions,
	App\Model,
	Nette\Security\Passwords,
	Nette\Diagnostics\Debugger;



/**
 * Registration presenter.
 */
class RegisterPresenter extends \App\Presenters\BasePresenter
{
	/** @var Model\UserManager */
	private $userManager;

	public function __construct(Model\UserManager $userManager)
	{
		$this->userManager = $userManager;
	}



	public function renderDefualt()
	{
		$this['breadcrumbs']->add('registrácia', ':Register:default');
	}
	


	protected function createComponentRegistForm()
	{
		$form = new Nette\Application\UI\Form;              
		$form->addText('user_name', 'Meno:')
			->setRequired('Vyplňte prosím meno.')
			->setAttribute('class', 'formEl');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadajte prosím heslo.')
			->addRule($form::MIN_LENGTH, 'Zadajte prosím heslo s minimálne %d znakmi', 3)
			->setAttribute('class', 'formEl');
		
		$form->addPassword('password2', 'Zopakujte heslo:')
			->setRequired('Zadajte prosím heslo.')
			->addRule($form::EQUAL, 'Heslá sa nezhodujú. Zopakujte prosím kontrolu.', $form['password'])
			->setAttribute('class', 'formEl');
			
		$form->addText('email', 'Email:')
			->setRequired('Zadajte prosím emailovú adresu.')
			->addRule($form::EMAIL, 'Nezadali ste platnú mailovú adresu. Skontrolujte si ju prosím.', $form['password'])
			->setAttribute('class', 'formEl');
			
		$form->addSubmit('send', 'Registrovať')
			->setAttribute('class', 'formElB');

		$form->onSuccess[] = $this->registFormSucceeded;
		return $form;
	}


	public function registFormSucceeded($form)
	{
		$values = $form->getValues();
		if(isset($values->role) && !$this->user->isAllowed('user', 'create'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenia pre prideľovanie užívateľských rolí.');
		}

		try
		{
			$this->userManager->add($values);
		}
		catch(Exceptions\DuplicateEntryException $e)
		{
			if($e->getMessage() == 'user_name')
			{
				$form->addError('Meno '.$values['user_name'].' je už obsadené. Vyberte si prosím iné.');
			}
			elseif($e->getMessage() == 'email')
			{
				$form->addError('Email '.$values['email'].' je už zaregistrovaný. Musíte uviesť unikátny email.');
			}
			return;
		}

		$this->flashMessage('Vitajte '.$values['user_name'].'. Vaša registrácia bola úspešná, môžete sa prihlásiť.');
		$this->redirect('Sign:in');

	}

} 



