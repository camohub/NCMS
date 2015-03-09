<?php

namespace App\BlogModule\Presenters;

use	Nette,
	App\Model,
	Nette\Utils\Paginator;
use Tracy\Debugger;

/**
 * Autor presenter.
 */

class AuthorsPresenter extends \App\Presenters\BasePresenter
{


	public function startup()
	{
		parent::startup();

		$categories = new Model\Categories($this->database);

		$currSection = $categories->findOneByUrl($this->getName());
		$this['menu']->onlyActiveSection($currSection);

		$this['breadcrumbs']->add('Autori', ':Blog:Authors:default');
	}



	public function renderDefault()
	{
		$users = new Model\Users($this->database);

		$this->template->users = $users->findAll();
		$this->template->half = ceil(count($this->template->users)/2);

		$this->setHeaderTags($desc = 'Autori blogu', $title = 'Autori');
	}



	public function renderShow($id, $title)
	{
		$users = new Model\Users($this->database);
		if(!$user = $users->findOneBy(array('id' => (int)$id)))
		{
			throw new Nette\Application\BadRequestException('Požadovaný záznam nebol nájdený.');
		}

		$blogArticles = new Model\BlogArticles($this->database);

		$articles = $blogArticles->findBy(array('users_id' => (int)$id));
		$this->template->articles = $articles;
		$this->template->author = $title;

		$this['breadcrumbs']->add($title, ':Blog:Autori:show '.$id.', '.$title);
		$this->setHeaderTags($desc = $title.' - autor');
	}


}