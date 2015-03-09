<?php



namespace App\AdminModule\Presenters;



use	Nette,
	App,
	Nette\Application\UI\Form,
	App\Model\Categories,
	Nette\Utils\Strings,
	Tracy\Debugger;



class MenuPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{

	/** @var  App\Model\Categories */
	protected $categories;

	/** @var  Array */
	protected $getArray;



	public function startup()
	{
		if(!$this->user->isAllowed('menu', 'edit'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie editovať položku menu.');
		}

		parent::startup();
		$this->categories = new Categories($this->database);
		$this['breadcrumbs']->add('Spravovať menu', ':Admin:Menu:default');
	}



	public function renderDefault()
	{
		// Necessary for snippet with form (?)
		$this->template->_form = $this['createSectionForm'];

		$arr = $this->getArray = $this->categories->getArray($admin = true);

		$this->template->menuArr = $arr;
		$this->template->section = $arr[0];
	}



	public function handlePriority()
	{
		if($this->isAjax() && isset($_POST['menuItem']))
		{
			try {
				$iterator = 1;
				foreach($_POST['menuItem'] as $key => $val)
				{
					// if the array is large it would be better to update only changed items
					$this->categories->update((int)$key, array('parent_id' => (int)$val, 'priority' => $iterator));
					$iterator++;
				}
				$this->flashMessage('Zmeny v menu boli uložené.');
			}
			catch(\Exception $e){
				Debugger::log($e->getMessage(), 'error');
				$this->flashMessage('Pri ukladaní údajov došlo k chybe. Funkčnosť aplikácie by to nemalo ovplyvniť, ale pre istotu kontaktujte adminístrátora.');
			}

			// dont redraw adminEditMenu block cause js doesnt work with dynamically added list. It must be refreshed.
			$this->redrawControl('menu');

		}

	}



	public function handleCreate($module_id)
	{
		if($module_id)
		{
			if (!$this->getArray) $this->getArray = $this->categories->getArray();
			$getArray = $this->getArray;

			foreach ($getArray as $key => $val) {
				foreach ($val as $row) {
					if ($row->module_id != $module_id) {
						unset($getArray[$key][$row->id]);
					}
				}
				if (!$getArray[$key])  // empty array
				{
					unset($getArray[$key]);
				}
			}

			$params = $this->getCategoriesSelectParams($getArray, $getArray[0]);
			$this['createSectionForm']['parent_id']->setItems($params);
		}
		else
		{
			$this['createSectionForm']['parent_id']->setPrompt('Najprv vyberte modul.')
				->setItems(array());
		}
		$this->redrawControl('create_parent');
	}



	public function actionVisibility($id)
	{
		$row = $this->categories->findOneBy(array('id' => (int)$id), 'admin');

		$visible = $row->visible == 1 ? 0 : 1;
		$this->categories->update((int)$id, array('visible' => $visible));

		$this->flashMessage('Zmenili ste viditeľnosť položky.');
		$this->redirect(':Admin:Menu:default');

	}



	public function actionDelete($id)
	{
		$row = $this->categories->findOneBy(array('id' => (int)$id), 'admin');
		if($row->app == 1)
		{
			$this->flashMessage('Položka je natívnou súčasťou aplikácie. Neni možné ju zmazať. Môžete ju ale skryť.');
		}
		else
		{
			$count = $this->categories->delete((int)$id);
			$this->flashMessage('Položka s názvom '.$row->title.' bola odstránená. Celkový počet odstránených položiek '.$count);
		}

		$this->redirect(':Admin:Menu:default');

	}



//////Protected/Private///////////////////////////////////////////////

	/**
	 * @desc produces an array of categories in format required by form->select
	 * @param $wholeArr
	 * @param $secArr
	 * @param array $params
	 * @param int $lev
	 * @return array
	 */
	protected function getCategoriesSelectParams($wholeArr, $secArr, $params = array(), $lev = 0)
	{
		if(!$params)
		{
			$params[0] = 'Sekcia Top';
		}
		foreach($secArr as $row)
		{
			$params[$row->id] = str_repeat('>', $lev * 1).$row->title;
			if(isset($wholeArr[$row->id]))
			{
				$params = $this->getCategoriesSelectParams($wholeArr, $wholeArr[$row->id], $params, $lev+1);
			}
		}

		return $params;
	}


//////Control////////////////////////////////////////////////////////////////

	protected function createComponentCreateSectionForm()
	{
		$this->getArray = $this->getArray ? $this->getArray : $this->categories->getArray();

		$form = new Nette\Application\UI\Form();

		$form->addText('title', 'Zvoľte názov')
		->addRule(Form::FILLED, 'Pole meno musí byť vyplnené.')
		->setAttribute('class', 'b4 c3');


		$form->addSelect('module_id', 'Vyberte modul', $this->categories->findModules()->fetchPairs('id', 'title'), 3)
		->setAttribute('class', 'w100P');


		$form->addSelect('parent_id', 'Vyberte pozíciu')
		->setAttribute('class', 'w100P');

		$form->addSubmit('sbmt', 'Uložiť')
		->setAttribute('class', 'dIB button1 pH20 pV5');

		$form->onSuccess[] = $this->createSectionFormSucceeded;

		return $form;
	}


	public function createSectionFormSucceeded($form)
	{
		$values = $form->getHttpData();
		unset($values['sbmt']);
		unset($values['do']);

		$module = $this->categories->findOneModuleBy(array('id' => $values['module_id']));

		$values['priority'] = 0;
		$values['url'] = 'Nezadane';

		try {
			$row = $this->categories->add($values);
			$this->categories->update($row->id, array('url' => ':'.Strings::firstUpper($module->title).':Default:default '.$row->id));
		}
		catch(\Exception $e) {
			$this->flashMessage('Pri ukladaní došlo k chybe. Fungovanie alikácie by to nemalo ovplyvniť. Kontaktujte prosím administrátora.', 'error');
			Debugger::log($e->getMessage(), 'error');
			return $form;
		}

		$this->flashMessage('Sekcia bola vytvorená.');
		$this->redirect('this');

}


///////Control///////////////////////////////////////////////////////////////////////



	public function createComponentEditSectionForm()
	{
		$form = new Form();

		$form->addText('title', 'Zmeňte názov')
		->addRule(Form::FILLED, 'Pole názov musí byť vyplnené')
		->setAttribute('class', 'b4 c3');

		$form->addHidden('id')
		->addRule(Form::FILLED, 'Došlo k chybe. Sekcia nemá nastavené id. Skúste kliknúť na ikonu edit znova prosím.');

		$form->addSubmit('sbmt', 'Uložiť')
		->setAttribute('class', 'dIB button1 pH20 pV5');

		$form->onSuccess[] = $this->editSectionFormSucceeded;

		return $form;

	}



	public function editSectionFormSucceeded($form)
	{
		$values = $form->getValues();

		try {
			$this->categories->update((int)$values->id, array('title' => $values->title));
		}
		catch(\Exception $e) {
			Debugger::log($e->getMessage(), 'error');
			$form->addError('Pri ukladaní došlo k chybe. Fungovanie aplikácie by to nemalo ovplyvniť. Kontaktujte administrátora.');
			return $form;
		}

		$this->flashMessage('Sekcia bola upravená.');
		$this->redirect('this');

	}


}
