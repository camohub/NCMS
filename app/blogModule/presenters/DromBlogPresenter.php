<?php
namespace App\BlogModule\Presenters;

use	Nette,
	Nette\Utils\Validators,
	Nette\Caching\Cache,
	Nette\Diagnostics\Debugger;

class DromBlogPresenter extends \App\Presenters\BasePresenter
{

	/** @var Nette\Database\Context */
	private $database;
	/** @var Nette\Caching\IStorage @inject */
	public $storage;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}
	
	public function startup()
	{
		parent::startup();
		
	}

	public function renderDefault($id)
	{
		$selection = $this->database->table('content')->select('content.title, :menu.id')->where(':menu.id = ?', 1);
		$this->template->selection = $selection;

	}
	
	public function renderShow($id, $title)
	{

	}

}
