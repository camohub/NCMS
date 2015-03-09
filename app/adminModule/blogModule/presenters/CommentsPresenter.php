<?php
namespace App\AdminModule\BlogModule\Presenters;

use	Nette,
	App,
	Nette\Application\UI\Form,
	Nette\Utils\Finder,
	Tracy\Debugger;

class CommentsPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{

	/** @var  App\Model\BlogArticles */
	protected $blogArticles;

	/** @var  App\Model\BlogComments */
	protected $blogComments;

	/** @var  Nette\Database\Table\IRow */
	protected $article;



	public function startup()
	{
		parent::startup();
		$this->blogArticles = new App\Model\BlogArticles($this->database);
		$this->blogComments = new App\Model\BlogComments($this->database);

		$this['breadcrumbs']->add('Články', ':Admin:Blog:Articles:default');
	}



	public function renderDefault($id)
	{


		$article = $this->article ? $this->article : $this->blogArticles->findOneBy(array('id' => (int)$id), 'admin');

		$this->template->article = $article;

		$this['breadcrumbs']->add('Komentáre', ':Admin:Blog:Comments:default '.$article->id);
	}


	/**
	 * @secured
	 * @param $id
	 * @param $comment_id
	 * @throws App\Exceptions\AccessDeniedException
	 */
	public function handleDelete($id, $comment_id)
	{
		if(!$this->user->isAllowed('comment', 'delete'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie mazať komentáre.');
		}

		$this->article = $article = $this->blogArticles->findOneBy(array('id' => (int)$id), 'admin');

		if( !($article->users_id == $this->user->id || $this->user->isInRole('admin')) )
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte právo zmazať tento komentár.');
		}


		try {
			$this->blogComments->delete((int)$comment_id);
			$this->flashMessage('Komentár bol zmazaný.');
		}
		catch(\Exception $e) {
			Debugger::log($e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Pri mazaní komentára došlo k chybe.', 'error');
		}


		if($this->isAjax())
		{
			$this->redrawControl('comments');
		}
		else
		{
			$this->redirect('this');
		}

	}


///////////component//////////////////////////////////////////////////////////




}
