<?php

namespace App\Presenters;

use Nette,
	App;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @var Nette\Database\Context @inject */
	public $database;

	/** @var  Nette\Security\IAuthorizator */
	public $athorizator;




	public function startup()
	{
		parent::startup();
		$this['breadcrumbs']->add('Úvod', ':Default:default');
	}



	/**
	 * @param $url
	 * @return bool
	 */
	public function isSectionCurrent($url)
	{
		$url = ltrim($url, ':');
		$section = explode(':', $this->getName())[0];
		return stripos($url, $section) === 0;
		//or return \Nette\Utils\Strings::startsWith($this->getName(), $url);
	}



	/**
	 * @return \App\Controls\Menu
	 */
	public function createComponentMenu()
	{
		$categories = new App\Model\Categories($this->database);
		return  new App\Controls\Menu($categories);
	}


	/**
	 * @return App\Controls\Breadcrumbs
	 */
	public function createComponentBreadcrumbs()
	{
		return new App\Controls\Breadcrumbs();
	}



	/**
	 * @param null $class
	 * @return Nette\Application\UI\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->addFilter('datum', function ($s, $lang = 'sk') {
			$needles = array('Monday', 'Tuesday', 'Wensday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'January', 'February', 'March', 'April', 'May', 'Jun', 'July', 'August', 'September', 'October', 'November', 'December',  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Mon', 'Tue', 'Wen', 'Thu', 'Fri', 'Sat', 'Sun');
			$sk = array('pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota', 'nedeľa', 'január','február','marec', 'apríl', 'máj', 'jún', 'júl', 'august', 'september', 'október', 'november', 'december', 'jan.', 'feb.', 'mar.', 'apr.', 'máj', 'júl', 'aug.', 'sep.', 'okt.', 'nov.', 'dec.', 'Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne');

			return str_replace($needles, $$lang, $s);
		});
		return $template;
	}


}
