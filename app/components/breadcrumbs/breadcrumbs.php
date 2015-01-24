<?php

namespace App\Controls;

use	Nette,
	App;


class Breadcrumbs extends Nette\Application\UI\Control
{

	/** @var array */
	protected $links = array();


	/**
	 * returns Nette\Application\UI\ITemplate
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/breadcrumbs.latte');
		$this->template->links = $this->links;
		$this->template->render();
	}


	/**
	 * @param $text
	 * @param $link
	 */
	public function add($text, $link)
	{
		$this->links[] = array(
			'text' => $text,
			'link' => $link,
		);
	}



	public function remove($key)
	{
		unset($this->links[$key]);
	}

}
