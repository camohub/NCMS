<?php

namespace App\Model;

use Nette,
	App,
	Exceptions,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


/**
 * Facebook users management.
 */
class UserManagerFB extends Nette\Object
{
	/** Exception error code */
	const IDENTITY_NOT_FOUND = 1,
		INVALID_CREDENTIAL = 2,
		FAILURE = 3,
		NOT_APPROVED = 4;


	const TABLE_NAME = 'users',
		ROLE = 3,
		COLUMN_ID = 'id',
		COLUMN_FB_ID = 'fb_id',
		COLUMN_NAME = 'user_name',
		COLUMN_PASSWORD = 'password',
		COLUMN_EMAIL = 'email',
		COLUMN_ACTIVE = 'active';


	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws App\Exceptions\DuplicateEntryException
	 * @throws \Exception
	 */
	public function authenticate(array $credentials)
	{
		// $email, $user_name, $fb_id
		extract($credentials);

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_FB_ID, $fb_id)->fetch();

		if (!$row) {
			$row = $this->add($credentials);
		}

		$userArr = $row->toArray();

		return new Nette\Security\Identity($row[self::COLUMN_ID], array('registered'), $userArr);
	}


	/**
	 * @param $params
	 * @param int $trial
	 * @return bool|int|Nette\Database\Table\IRow
	 * @throws App\Exceptions\DuplicateEntryException
	 * @throws \Exception
	 */
	public function add($params, $trial = 1)
	{
		extract($params);

		$user_name = $trial === 1 ? $user_name : $user_name .' '. $trial;
		$password = NULL;

		$this->database->beginTransaction();
		try {
			$row = $this->database->table(self::TABLE_NAME)->insert(array(
							self::COLUMN_NAME => $user_name,
							self::COLUMN_PASSWORD => $password,
							self::COLUMN_EMAIL => $email,
							self::COLUMN_FB_ID => $fb_id,
							self::COLUMN_ACTIVE => 1,
			));

			if($trial > 30) throw new \Exception('Unexpected error. Variable $trial in UserManagerFB->add() reached value 30.');
		}
		catch(\PDOException $e)	{
			// This catch ONLY checks duplicate entry to fields with UNIQUE KEY
			$this->database->rollBack();
			$info = $e->errorInfo;

			// mysql==1062  sqlite==19  postgresql==23505
			if ($info[0] == 23000 && $info[1] == 1062)
			{
				// if duplicate is name calls add() and the name join with $trial value // it makes ie. Jozef Mak 3
				if( $this->database->table(self::TABLE_NAME)->where('user_name = ?', $params['user_name'])->fetch() )
				{
					$this->add($params, ++$trial);
				}
				// else duplicate email throws exception
				elseif( $this->database->table(self::TABLE_NAME)->where('email = ?', $params['email'])->fetch() )
				{
					$msg = 'email';	$code = 1;
					throw new App\Exceptions\DuplicateEntryException($msg, $code);
				}

			}
			else { throw $e; }
		}

		try	{
			$this->database->table('users_acl_roles')->insert(array('users_id' => $row->id, 'acl_roles_id' => self::ROLE));
		}
		catch(\Exception $e) {
			$this->database->rollBack();
			throw $e;
		}

		$this->database->commit();
		return $row;

	}

}
