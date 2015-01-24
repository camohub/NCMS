<?php

namespace App\BlogModule\Presenters;

use	Nette,
	App\Model,
	Nette\Utils\Paginator;

/**
 * Autor presenter.
 */

class AuthorsPresenter extends \App\Presenters\BasePresenter
{



	public function startup()
	{
		parent::startup();

		$categories = new Model\Categories($this->database);

		$currSection = $categories->getCurrentSection($this->getName());
		$this['menu']->onlyActiveSection($currSection);
	}



	public function renderDefault()
	{
		$users = new Model\Users($this->database);

		$this['breadcrumbs']->add('Autori', 'Blog:Autori:default');

		$this->template->users = $users->findAll();
		$this->template->half = ceil(count($this->template->users)/2);
	}



	public function renderShow($id)
	{
		$blogArticles = new Model\BlogArticles($this->database);

		$this['breadcrumbs']->add('Autori', 'Blog:Autori:show');

		$articles = $blogArticles->findBy(array('users_id' => (int) $id) );
		$this->template->articles = $articles;
		$this->template->autor = $id;
	}

}