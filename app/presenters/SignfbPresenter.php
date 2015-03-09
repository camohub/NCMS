<?php

namespace App\Presenters;

use Nette,
	App,
	Facebook,
	Tracy\Debugger;


/**
 * Facebook Sign presenter.
 */
class SignfbPresenter extends BasePresenter
{
	/** Facebook credentials */
	CONST 	APPID = '344387805765697',
			APPSECRET = '61c89e18a89237de5a5c68a5a48fc370';

	/** @var  App\Model\UserManagerFB */
	protected $userManagerFB;



	public function startup()
	{
		parent::startup();
		$this->userManagerFB = new App\Model\UserManagerFB($this->database);
	}


	/**
	 * @desc This method is called via ajax from fb js sdk (in BasePresenter)
	 * @param null $id
	 * @throws Facebook\FacebookRequestException
	 * @throws Nette\Application\AbortException
	 */
	public function actionIn($id = NULL)
	{
		Facebook\FacebookSession::setDefaultApplication(self::APPID, self::APPSECRET);

		$helper = new Facebook\FacebookJavaScriptLoginHelper();

		try {
			$session = $helper->getSession();
		} catch(FacebookRequestException $ex) {
			// When Facebook returns an error
			Debugger::log('FacebookRequestException: ' . $ex->getMessage(), 'error');
			$this->sendJson(array('errCode' => 1, 'error' => 'Prihlasovanie cez Facebook API zlyhalo.') );
			// No need to call terinate(). SendJson() already calls it.
		} catch(\Exception $ex) {
			// When validation fails or other local issues
			Debugger::log('FacebookRequestException: ' . $ex->getMessage(), 'error');
			$this->sendJson(array('errCode' => 1, 'error' => 'Prihlasovanie cez Facebook API zlyhalo.') );
		}

		if (isset($session) && $session) {
			// Logged in.
			try {
				$request = new Facebook\FacebookRequest($session, 'GET', '/me');
				$response = $request->execute();
				$fbUser = $response->getGraphObject(Facebook\GraphUser::className());
			}
			catch(\Exception $e) {
				$this->sendJson(array('errCode' => 1, 'error' => 'Prihlasovanie cez Facebook API zlyhalo.'));
			}

			$userArr = array(
				'fb_id' => $fbUser->getProperty('id'),
				'email' => $fbUser->getProperty('email'), // email|NULL
				'user_name' => $fbUser->getProperty('name'),
			);

			if(empty($userArr['email']))
			{
				$this->sendJson(array('errCode' => 0, 'error' => 'Aplikácia potrebuje kôli autentizácii Vašu emailovú adresu. Bez emailu Vás nieje možné prihlásiť cez Facebook.'));
			}

			try {
				$identity = $this->userManagerFB->authenticate($userArr);

				if ($identity->active == 0)
				{
					throw new App\Exceptions\AccessDeniedException('Účet cez ktorý sa pokúšate prihlásiť, je blokovaný.', 2);
				}

				$this->getUser()->setExpiration('30 minutes', TRUE);
				$this->getUser()->login($identity);
				// Don't call sendJson() from try. It throws an Exception

			} catch (App\Exceptions\DuplicateEntryException $e) {
				$this->sendJson(array('errCode' => 0, 'error' => $e->getCode() == 1 ? 'Pri pokuse registrovať Váš email došlo k chybe. Tento email je už registrovaný.' : $e->getMessage()) );

			} catch (App\Exceptions\AccessDeniedException $e) {
				// Be careful what can be displayed.
				$this->sendJson(array('errCode' => 0, 'error' => $e->getMessage()) );

			} catch (\Exception $e) {
				// Be careful whan can be displayed.
				$this->sendJson(array('errCode' => 0, 'error' => 'Prihlasovanie cez Facebook zlyhalo.'));
				Debugger::log($e->getMessage());
			}

			$this->flashMessage('Ste prihlásený ako '.$this->user->identity->user_name);
			// sendJson must not be in try block cause it throws an Nette\Application\AbortException
			$this->sendJson(array('ok' => 'loged in', '_fid' => $this->params[self::FLASH_KEY]));
		}
		else
		{
			$this->sendJson(array('errCode' => 0, 'error' => 'Nepodarilo sa overiť vašu identitu na Facebooku.'));
		}
		$this->terminate();

	}



	/**
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 * @desc This method is not used today !!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	 */
	public function renderIn($id = NULL)
	{
		$this->setHeaderTags(NULL, NULL, $robots = 'noindex, nofolow');
		$this['breadcrumbs']->add('Prihlásenie cez Facebook', ':Signfb:in');

		Facebook\FacebookSession::setDefaultApplication('344387805765697', '61c89e18a89237de5a5c68a5a48fc370');

		// Facebook API needs to be redirected first to Facebook and then back with id = 1 to make a session
		if(!$id)
		{
			$this->getSession('Signfb')->referer = $this->context->httpRequest->referer ? $this->context->httpRequest->referer->absoluteUrl : NULL;

			$helper = new Facebook\FacebookRedirectLoginHelper( $this->link('//:Signfb:in', 1) );
			$this->redirectUrl($helper->getLoginUrl(array('scope' => 'public_profile,email') ) );
		}
		else
		{
			$this->template->fbLogin = 0;

			$referer = $this->getSession('Signfb')->referer;
			$helper = new Facebook\FacebookRedirectLoginHelper($this->link('//:Signfb:in', 1) );

			try {
				$session = $helper->getSessionFromRedirect();
			} catch (Facebook\FacebookRequestException $ex) {
				Debugger::log('FacebookRequestException: ' . $ex->getMessage(), 'error');
				$this->flashMessage('Pri prihlasovaní cez Facebook API došlo k chybe. '.$ex->getMessage());
				$referer ? $this->redirectUrl($referer) : $this->redirect('Default:default');
			} catch (\Exception $ex) {
				Debugger::log('AnotherFacebookException: ' . $ex->getMessage(), 'error');
				$this->flashMessage('Pri prihlasovaní cez Facebook API došlo k chybe. Skúste to prosím ešte raz.'. $ex->getMessage());
				$referer ? $this->redirectUrl($referer) : $this->redirect('Default:default');
			}

			if (isset($session) && $session) {
				$request = new Facebook\FacebookRequest($session, 'GET', '/me');
				$response = $request->execute();
				$fbUser = $response->getGraphObject(Facebook\GraphUser::className());

				$userArr = array(
					'fb_id' => $fbUser->getProperty('id'),
					'email' => $fbUser->getProperty('email'),
					'user_name' => $fbUser->getProperty('name'),
				);

				if(empty($userArr['email']))
				{
					$this->flashMessage('Aplikácia potrebuje kôli autentizácii Vašu emailovú adresu.
					Bez emailu Vás nieje možné prihlásiť cez Facebook.');
					$referer ? $this->redirectUrl($referer) : $this->redirect('Default:default');
				}

				try {
					$identity = $this->userManagerFB->authenticate($userArr);
					if($identity->active == 0) throw new App\Exceptions\AccessDeniedException('Učet ku ktorému chcete pristúpiť bol zablokovaný.');
					$this->getUser()->login($identity);
					$this->template->fbLogin = 1;
				}
				catch(App\Exceptions\DuplicateEntryException $e) {
					$this->flashMessage($e->getMessage());
					$referer ? $this->redirectUrl($referer) : $this->redirect('Default:default');
				}
				catch(\Exception $e) {
					$this->flashMessage('Pri prihlasovaní došlo k chybe. Skúste to prosím ešte raz.');
					Debugger::log($e->getMessage(), 'error');
					$referer ? $this->redirectUrl($referer) : $this->redirect('Default:default');
				}


				$referer ? $this->redirectUrl($referer) : $this->redirect('Default:default');

			}
		}
	}


//////component//////////////////////////////////////////////////////////////


}
