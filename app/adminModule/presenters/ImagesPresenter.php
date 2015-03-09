<?php



namespace App\AdminModule\Presenters;



use	Nette,
	App,
	Nette\Application\UI\Form,
	Nette\Utils\Image,
	Nette\Diagnostics\Debugger;



class ImagesPresenter extends App\AdminModule\Presenters\BaseAdminPresenter
{

	/** @var  App\Model\Images */
	protected $imagesModel;



	public function startup()
	{
		if(!$this->user->isAllowed('image', 'add'))
		{
			throw new App\Exceptions\AccessDeniedException('Nemáte oprávnenie editovať obrázky.');
		}
		parent::startup();

		$this->imagesModel = new App\Model\Images($this->database);

	}



	public function renderBlog()
	{
		$this['breadcrumbs']->add('Obrázky - blog', ':Admin:Images:blog');

	}



	public function renderEshop()
	{

	}


//////Protected/Private///////////////////////////////////////////////



//////Control/////////////////////////////////////////////////////////

	public function createComponentInsertForm()
	{
		$form = new Form();

		$form->addUpload('images', 'Vyberte obrázok', TRUE)
		->setRequired('Nevybrali ste žiadny obrázok.')
		->addRule(Form::IMAGE, 'Obrázok musí biť JPEG, PNG nebo GIF.')
		->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru je 200 kB.', 200 * 1024 /* v bytech */)
		->setAttribute('class', 'button1 fWB');

		$form->addSubmit('sbmt', 'Ulož')
		->setAttribute('class', 'button1 fWB');

		$form->onSuccess[] = $this->insertFormSucceeded;

		return $form;

	}



	public function insertFormSucceeded($form)
	{

		$images = $form->getValues()->images;

		if(count($images) > 5)
		{
			$this->flashMessage('Naraz nemôžete ukladať viac ako 5 obrázkov.', 'error');
			return;
		}


		$path = $this->context->parameters['wwwDir'] . '/images/blog';

		$errors = 0;
		foreach($images as $image)
		{
			if($image->isOk())
			{
				$name = $image->getName();
				$sName = $image->getSanitizedName();
				$tmpName = $image->getTemporaryFile();

				try {
					$this->database->beginTransaction();

					try {
						$this->imagesModel->insert(array(
							'module_id' => 1,
							'name' => $sName,
						));
					}
					catch(App\Exceptions\DuplicateEntryException $e)
					{
						$this->flashMessage('Súbor s názvom '.$name.' už existuje. Súbor nebol uložený, pretože by ste prepísali iný súbor.', 'error');
						$this->database->rollBack();
						continue;
					}

					$img = Image::fromFile($tmpName);

					$img->resize(600, 450);  // Keeps ratio == one of the sides can be shorter, but none will be longer
					$img->save($path . '/' .$sName);

					$img->resize(150, NULL);  // Width will be 150px and height keeps ratio
					$img->save($path . '/thumbnails/' .$sName);

					$this->flashMessage('Obrázok '.$sName.' bol uložený na server.');
					$this->database->commit();
				}
				catch(\Exception $e) {
					$this->database->rollBack();
					Debugger::log($e->getMessage(), 'ERROR');
					$this->flashMessage('Pri ukladaní súboru '.$name.' došlo k chybe. Súbor nebol uložený.');
					unlink($path.'/'.$sName);  // if something is saved, delete it
					unlink($path.'/thumbnails/'.$sName);
				}
			}
			else
			{
				$errors++;
			}
		}

		if($errors)
		{
			$s = $errors == 1 ? ' súbor ' : ($errors < 5 ? ' súbory ' : ' súborov ');
			$this->flashMessage($errors.$s.'sa nepodarilo uložiť na server.', 'error');
		}
		$this->redirect('this');

	}

}
