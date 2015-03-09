<?php

namespace App\Controls;

use	Nette,
	App;
use Tracy\Debugger;


class Poll extends Nette\Application\UI\Control
{

	/** @var Nette\Database\Context */
	protected $database;

	/** @var  int|NULL */
	protected $id;

	/** @var bool  */
	protected $result = FALSE;


	public function __construct(Nette\Database\Context $db, $that, $name = NULL)
	{
		parent::__construct();

		$this->database = $db;
		$this->id = $name ? explode('_', $name)[1] : $name;
	}



	public function handleVote($q_id, $p_id)
	{
		$ip = $this->presenter->context->httpRequest->remoteAddress;
		if($ip)
		{
			try {
				$this->database->table('polls_responses')->insert(array(
					'polls_question_id' => (int)$q_id,
					'poll_id' => $p_id,
					'ip' => $ip,
				));
			}
			catch(\Exception $e) {

			}
		}
		$this->result = TRUE;
	}



	/**
	 * returns Nette\Application\UI\ITemplate
	 */
	public function render()
	{
		$latte = $this->result ? '/result.latte' : '/poll.latte';
		$this->template->setFile(__DIR__ . $latte);
		$poll = $this->database->table('polls')->where('id = ? OR poll_id = ?', $this->id, $this->id);
		$pollArray = array();
		foreach($poll as $row)
		{
			$pollArray[$row->poll_id][] = $row;
		}
		$this->template->pollArray = $pollArray;
		$this->template->id = (int)$this->id;
		$this->template->render();
	}

}
