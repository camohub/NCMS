<?php
namespace App\BlogModule\Presenters;

use	Nette,
	App,
	Tracy\Debugger,
	App\Model;


class ArticlesPresenter extends \App\Presenters\BasePresenter
{

	/** @var Nette\Caching\IStorage @inject */
	public $storage;

	/** @var  App\Model\BlogArticles */
	protected $blogArticles;

	/** @var  Array */
	protected $optCompArray;



	public function startup()
	{
		parent::startup();
		$this->blogArticles = new Model\BlogArticles($this->database);

		$this->optCompArray = $this->getOptionalComponents($this->name);
	}



	public function renderDefault()
	{
		$this['breadcrumbs']->add('Članky', ':Blog:Articles:default');

		$articles = $this->blogArticles->findAll();

		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 3;
		$paginator->itemCount = count($articles);

		$this->template->articles = $articles->limit($paginator->itemsPerPage, $paginator->offset);


		$this->setHeaderTags($metaDesc = 'Blog - najnovšie články', $title = 'Najnovšie články');
		$this->template->optCompArray = $this->getOptionalComponents($this->name) ? $this->getOptionalComponents($this->name) : $this->optCompArray;
	}



	public function renderShow($id, $title)
	{
		$article = $this->blogArticles->findOneBy(array('id' => (int)$id));
		if(!$article)
		{
			throw new Nette\Application\BadRequestException('Požadovanú stránku sa nepodarilo nájsť.');
		}
		$this->template->article = $article;

		$this->template->comments = $article->related('blog_comments.blog_articles_id')->order('created_at');

		$this['breadcrumbs']->add('Članky', ':Blog:Articles:default');
		$this['breadcrumbs']->add($article->title, ':Blog:Articles:show '.$id);

		$this->setHeaderTags($metaDesc = $article->meta_desc);
		$this->template->fb = TRUE;
		$this->template->optCompArray = $this->getOptionalComponents($this->name.' '.$id) ? $this->getOptionalComponents($this->name.' '.$id) : $this->optCompArray;

	}



	public function renderLiked()
	{
		$this['breadcrumbs']->add('Obľúbené', ':Articles:liked');
	}



/////component/////////////////////////////////////////////////////////////////////////

	protected function createComponentCommentForm()
	{
		$form = new Nette\Application\UI\Form;

		$form->addTextArea('content', 'Komentár:')
			->setRequired('Komentár je povinná položka')
			->setAttribute('class','w75P h60');

		$form->addSubmit('send', 'Odoslať');

		$form->onSuccess[] = $this->commentFormSucceeded;

		return $form;
	}

	public function commentFormSucceeded($form)
	{
		if(!$this->user->isAllowed('comment', 'add'))
		{
			$this->flashMessage('Pridávať komentáre môžu iba regirovaní užívatelia.', 'error');
			throw new App\Exceptions\AccessDeniedException('Pridávať komentáre môžu iba regirovaní užívatelia.');
		}
		$values = $form->getValues();
		$id = (int)$this->getParameter('id');

		$blogArticles = new Model\BlogArticles($this->database);
		$params = array('blog_articles_id' => $id,
				'users_id' => $this->getUser()->id,
				'name' => $this->getUser()->identity->user_name,
				'email' => $this->getUser()->identity->email,
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
