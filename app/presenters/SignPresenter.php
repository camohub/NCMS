<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	public function renderIn( $back_link = NULL )
	{
		$this['breadcrumbs']->add( 'Prihlásiť', 'Sign:in' );

		$this->template->fb = TRUE;
		$this->setHeaderTags( NULL, NULL, $robots = 'noindex, nofolow' );
	}



	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage( 'Boli ste odhlásený.' );
		if ( $back_link = $this->getParameter( 'back_link', NULL ) )
		{
			$this->restoreRequest( $back_link );
		}
		else
		{
			$this->redirect( 'Default:' );
		}
	}


//////component//////////////////////////////////////////////////////////////

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Nette\Application\UI\Form;

		$form->addProtection( 'Vypršal čas vyhradený pre odoslanie formulára. Z dôvodu rizika útoku CSRF bola požiadavka na server zamietnutá.' );

		$form->addText( 'user_name', 'Username:' )
			->setRequired( 'Please enter your username.' )
			->setAttribute( 'class', 'formEl' );

		$form->addPassword( 'password', 'Password:' )
			->setRequired( 'Please enter your password.' )
			->setAttribute( 'class', 'formEl' );

		$form->addCheckbox( 'remember', 'Keep me signed in' );


		if ( $backLink = $this->getParameter( 'back_link', NULL ) )
		{
			$form->addHidden( 'back_link' )
				->setDefaultValue( $this->getParameter( 'back_link', NULL ) );
		}

		$form->addSubmit( 'send', 'Prihlásiť' )
			->setAttribute( 'class', 'formElB' );

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array( $this, 'signInFormSucceeded' );
		return $form;
	}


	public function signInFormSucceeded( $form, $values )
	{
		if ( $values->remember )
		{
			$this->getUser()->setExpiration( '14 days', FALSE );
		}
		else
		{
			$this->getUser()->setExpiration( 0, TRUE );
		}

		try
		{
			$this->getUser()->login( $values->user_name, $values->password );
			$this->flashMessage( 'Vitajte ' . $values['user_name'] );
			if ( isset( $values['back_link'] ) )
			{
				// This throws an Exception. Be careful what yuo catch.
				$this->restoreRequest( $values['back_link'] );
			}
			else
			{
				// This throws an Exception. Be careful what you catch.
				$this->redirect( ':Default:' );
			}

		}
		catch ( Nette\Security\AuthenticationException $e )
		{
			$form->addError( $e->getMessage() );
		}
	}

}
