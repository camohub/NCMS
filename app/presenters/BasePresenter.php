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



	public function afterRender()
	{
		if ($this->isAjax() && $this->hasFlashSession())
			$this->redrawControl('flash');
	}



	/**
	 * @desc Used in menu detects name of module  section == module
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


/////////helper//////////////////////////////////////////////////////

	/**
	 * @desc Helper for latte translates names of months and days
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


////////components/////////////////////////////////////////////////////

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
	 * @param $name
	 * @return \NasExt\Controls\VisualPaginator
	 */
	protected function createComponentVp($name)
	{
		$control = new \NasExt\Controls\VisualPaginator($this, $name);
		// enable ajax request, default is false
		/*$control->setAjaxRequest();

		$that = $this;
		$control->onShowPage[] = function ($component, $page) use ($that) {
		if($that->isAjax()){
		$that->invalidateControl();
		}
		};   */
		return $control;
	}


}
