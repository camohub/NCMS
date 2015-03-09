<?php



namespace App\AdminModule\Presenters;



use	Nette,
	App,
	Nette\Application\UI\Form,
	Nette\Utils\Image,
	Tracy\Debugger;



class PollsPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{

	/** @var  App\Model\Polls */
	protected $pollsModel;



	public function startup()
	{
		if(!$this->user->isAllowed('poll', 'edit'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie editovať ankety.');
		}
		parent::startup();

		$this->pollsModel = new App\Model\Polls($this->database);

		$this['breadcrumbs']->add('Ankety', ':Admin:Polls:default');

	}



	public function renderDefault()
	{
		$this->template->pollsArray = $pollsArray = $this->pollsModel->getArray('admin');
		$this->template->polls = $pollsArray ? $pollsArray[NULL] : array();
	}



	public function renderEdit($id)
	{
		$this['breadcrumbs']->add('Editovať', ':Admin:Polls:edit '.$id);
	}



	public function renderAdd(){
		$this['breadcrumbs']->add('Pridať', ':Admin:Polls:add');
	}



	public function handleDelete($id, $question = FALSE)
	{
		$this->pollsModel->delete($id, $question);

	}


//////Protected/Private///////////////////////////////////////////////



//////Control/////////////////////////////////////////////////////////

	public function createComponentAddPollForm()
	{
		$id = $this->getParameter('id');  // $id/$poll are used to edit poll for defaults
		$poll = array();
		if($id)
		{
			$poll = $this->pollsModel->findOnePoll((int)$id, 'admin')->fetchPairs('id', 'title');
		}


		$form = new Form();

		$form->addTextArea('title', 'Názov ankety')
		->setRequired('Zadajte prosím názov ankety. Pole nemôže ostať prázdne.')
		->setAttribute('class', 'w75P mV10 b4 c3')
		->setDefaultValue(isset($poll[$id]) ? $poll[$id] : '');

		if($id)
		{
			$old_questions = $form->addContainer('old_questions');
			foreach($poll as $key => $val)
			{
				if($key == $id) continue;
				$old_questions->addTextArea($key, 'XXX')
				->setRequired()
				->setAttribute('class', 'w75P mV10 b4 c3')
				->setDefaultValue($val);
			}
		}

		$questions = $form->addContainer('questions');
		foreach(range(1,20) as $item)
		{
			$q = $questions->addTextArea($item,'Otázka '.$item);
			if($item == 1 && !$id) $q->setRequired('Anketa musí obsahovať minimálne jednu otázku. Doplňte ju prosím.');
			$q->setAttribute('class', 'mV10 w75P b4 c3 '.(($item == 1) ? ' dB ' : ' dN ' ) );
		}

		$form->addSubmit('sbmt', 'Ulož')
		->setAttribute('class', 'button1 fWB');

		$form->onSuccess[] = $this->insertFormSucceeded;

		return $form;

	}



	public function insertFormSucceeded($form)
	{
		$id = $this->getParameter('id');
		$values = $form->getValues();

		$this->database->beginTransaction();
		try {
			if($id)
			{
				$this->pollsModel->update(array('title' => $values->title), array('id' => (int)$id));
				foreach($values->old_questions as $oq_key => $oq_val)
				{
					$this->pollsModel->update(array('title' => $oq_val), array('id' => $oq_key));
				}
			}
			else
			{
				$row = $this->pollsModel->insert(array('title' => $values->title));
				$id = $row->id;
			}


			// This part is collective (spoločná)
			foreach($values->questions as $q)
			{
				if($q == '') continue;
				$this->pollsModel->insert(array('poll_id' => $id, 'title' => $q));
			}
		}
		catch(\Exception $e) {
			Debugger::log($e->getMessage());
			$this->database->rollBack();
			$this->flashMessage('Pri ukladaní došlo k chybe. Anketu sa nepodarilo uložiť.');
		}

		$this->database->commit();
		$id ? $this->flashMessage('Anketa bola upravená.') : $this->flashMessage('Anketa bola vytvorená.');
		$this->redirect(':Admin:Polls:default');

	}


}
