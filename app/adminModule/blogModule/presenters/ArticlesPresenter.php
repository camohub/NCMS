<?php
namespace App\AdminModule\BlogModule\Presenters;

use	Nette,
	App,
	Nette\Application\UI\Form,
	Nette\Utils\Finder,
	Nette\Diagnostics\Debugger;

class ArticlesPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{

	/** @var  App\Model\BlogArticles */
	public $blogArticles;

	/** @var  App\Model\Users */
	public $users;

	/** @var  Nette\Database\Table\ActiveRow */
	public $article;

	/** @var  Nette\Database\Table\Selection */
	protected $filteredArticles;



	public function startup()
	{
		parent::startup();
		$this->blogArticles = new App\Model\BlogArticles($this->database);
		$this->users = new App\Model\Users($this->database);

		$this['breadcrumbs']->add('Články', ':Admin:Blog:Articles:default');
	}



	public function renderDefault()
	{
		// filter is relevant only for admin if he sets some in ArticlesFilterForm
		// $filter == $form->getValues()
		if($filter = $this->getSession('Admin:Blog:Articles')->filter)
		{
			$articles = $this->blogArticles->findAll('admin', false);

			$articles = $filter->authors ? $articles->where('users_id', $filter->authors) : $articles;
			$articles = $filter->interval ? $articles->where('created > ?', time() - $filter->interval * 60*60*24) : $articles;
			$articles = $filter->order ? $articles->order(implode(',', $filter->order)) : $articles;

			$this->template->articles = $articles;

			if(!$filter->remember)  // if filter is not in session, settings will be lost for every redirect to renderDefault (i.e. actionVisibility do this obvious)
			{
				unset($this->getSession('Admin:Blog:Articles')->filter);
			}
		}
		else
		{
			$this->template->articles = $this->blogArticles->findBy(array('users_id' => $this->user->id), 'admin');
		}
	}



	public function actionCreate()
	{
		if(!$this->user->isAllowed('article', 'create'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie vytvárať články.');
		}

		$this['breadcrumbs']->add('Vytvoriť', ':Admin:Blog:Articles:create');

	}



	public function actionEdit($id)
	{
		if(!$this->user->isAllowed('article', 'edit'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie editovať články.');
		}

		$this->article = $this->blogArticles->findOneBy(array('id' => (int)$id), 'admin');

		if( !($this->article->users_id == $this->user->id || $this->user->isInRole('admin')) )
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte právo editovať tento článok.');
		}

		$this['articleForm']->setDefaults($this->article);

		$this['breadcrumbs']->add('Editovať', ':Admin:Blog:Articles:edit');

	}



	public function actionVisibility($id)
	{
		if(!$this->user->isAllowed('article', 'edit'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie editovať články.');
		}

		$this->article = $this->blogArticles->findOneBy(array('id' => (int)$id), 'admin');

		if( !($this->article->users_id == $this->user->id || $this->user->isInRole('admin')) )
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte právo editovať tento článok.');
		}

		$status = $this->article->status == 1 ? 0 : 1;
		$this->blogArticles->updateArticle(array('status' => $status), (int)$id);

		$this->redirect(':Admin:Blog:Articles:default');

	}



	public function actionDelete($id)
	{
		if(!$this->user->isAllowed('article', 'delete'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie mazať články.');
		}

		$this->article = $this->blogArticles->findOneBy(array('id' => (int)$id), 'admin');

		if( !($this->article->users_id == $this->user->id || $this->user->isInRole('admin')) )
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte právo zmazať tento článok');
		}

		$this->blogArticles->delete((int)$id);
		$this->flashMessage('Článok bol zmazaný.');
		$this->redirect(':Admin:Blog:Articles:default');
	}


///////////component//////////////////////////////////////////////////////////

	public function createComponentArticleForm()
	{
		$form = new Form;

		$form->addText('meta_desc', 'Popis:', 80)
		->setRequired();

		$form->addText('title', 'Nadpis:', 80)
		->setRequired();

		$form->addTextArea('content', 'Text:')
		->setRequired()
		->setAttribute('class', 'area600 editor');

		$form->addSubmit('sbmt', 'Uložiť');

		$form->onSuccess[] = $this->articleFormSucceeded;

		return $form;
	}



	public function articleFormSucceeded($form)
	{
		$values = $form->getValues();
		$id = (int)$this->getParameter('id');

		if($id)
		{
			try {
				$this->blogArticles->updateArticle($values, $id);
				$this->flashMessage('Článok bol upravený.');
			}
			catch(\Exception $e) {
				$form->addError('Pri ukladaní článku došlo k chybe.');
				Debugger::log($e->getMessage(), Debugger::ERROR);
				return $form;
			}
			$this->redirect('this');
		}
		else
		{
			try {
				$values['created'] = time();
				$values['users_id'] = $this->user->id;
				$this->blogArticles->insertArticle($values);
				$this->flashMessage('Článok bol vytvorený.');
			}
			catch(\Exception $e) {
				$form->addError('Pri ukladaní článku došlo k chybe.');
				Debugger::log($e->getMessage(), Debugger::ERROR);
				return $form;
			}
			$this->redirect(':Admin:Blog:Articles:default');
		}

	}

/////////component//////////////////////////////////////////////

	public function createComponentArticlesFilterForm()
	{
		$form = new Form;

		$authors = $this->users->findAll('admin')->fetchPairs('id', 'user_name');
		$form->addMultiSelect('authors','Autori', $authors, 7);

		$form->addMultiSelect('order', 'Zoradiť podľa', array(
			'users.user_name DESC' => 'Meno zostupne',
			'users.user_name ASC' => 'Meno vzostupne',
			'created DESC' => 'Vytvorený zostupne',
			'created ASC' => 'Vytvorený vzostupne',
		), 7);

		$form->addText('interval', 'Interval posledných x dní', 3)
		->addCondition(FORM::FILLED)
		->addRule(FORM::INTEGER, 'Hodnota v poľa "Interval" musí byť číslo');

		$form->addCheckbox('remember', 'Zapamätať si nastavenia');

		$form->addSubmit('sbmt', 'filtrovať');

		$form->onSuccess[] = $this->filterFormSucceeded;

		if($filter = $this->getSession('Admin:Blog:Articles')->filter) $form->setDefaults($filter);

		return $form;
	}



	public function filterFormSucceeded($form)
	{
		$this->getSession('Admin:Blog:Articles')->filter = $form->getValues();
	}

}
