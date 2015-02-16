<?php
namespace App\BlogModule\Presenters;

use	Nette,
	App,
	Nette\Utils\Validators,
	Nette\Caching\Cache,
	Nette\Diagnostics\Debugger,
	App\Model;


class ArticlesPresenter extends \App\Presenters\BasePresenter
{

	/** @var Nette\Caching\IStorage @inject */
	public $storage;

	/** @var  App\Model\BlogArticles */
	protected $blogArticles;



	public function startup()
	{
		parent::startup();
		$this->blogArticles = new Model\BlogArticles($this->database);
	}



	public function renderDefault()
	{
		$this['breadcrumbs']->add('Članky', 'Blog:Articles:default');

		$articles = $this->blogArticles->findAll();

		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 3;
		$paginator->itemCount = count($articles);

		$this->template->articles = $articles->limit($paginator->itemsPerPage, $paginator->offset);
	}



	public function renderShow($id, $title)
	{
		$this['breadcrumbs']->add('Članky', 'Blog:Articles:show');

		$article = $this->blogArticles->findOneBy(array('id' => (int)$id));
		$this->template->article = $article;

		$this->template->comments = $article->related('blog_comments.blog_articles_id')->order('created_at');
	}



	public function renderSection($id)
	{
		$articles = $this->blogArticles->findBy(array('blog_article_category.id', (int)$id));
	}


/////component/////////////////////////////////////////////////////////////////////////

	protected function createComponentCommentForm()
	{
		$form = new Nette\Application\UI\Form;

		$form->addText('name', 'Meno:')
		->setRequired('Meno je povinná položka');

		$form->addText('email', 'Email:');
		
		$form->addTextArea('content', 'Komentár:')
			->setRequired('Komentár je povinná položka')
			->setAttribute('class','area400');

		$form->addSubmit('send', 'Publikovať komentár');

		$form->onSuccess[] = $this->commentFormSucceeded;

		return $form;
	}

	public function commentFormSucceeded($form)
	{
		if(!$this->user->isAllowed('comment', 'add'))
		{
			throw new App\Exceptions\AccessDeniedException('Pridávať komentáre môžu iba regirovaní užívatelia.');
		}
		$values = $form->getValues();
		$id = (int)$this->getParameter('id');

		$blogArticles = new Model\BlogArticles($this->database);
		$params = array('blog_articles_id' => $id,
				'name' => $values->name,
				'email' => $values->email,
				'content' => $values->content,);
		$row = $blogArticles->insertComment($params);

		if($row)
		{
			$this->flashMessage('Ďakujeme za komentár', 'success');
			$this->redirect('this');
		}
		else
		{
			$form->addError('Došlo k chybe. Váš komentár sa nepodarilo odoslať. Skúste to prosím neskôr.');
		}
	}

}
