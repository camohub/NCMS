<?php

namespace App\Model;

use Nette,
	App,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


/**
 * Users management.
 * Do not use this class to manage users from social networks like FB
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	const
		TABLE_NAME = 'users',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'user_name',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role',
		COLUMN_EMAIL = 'email';


	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * @desc Performs an authentication. Do not use it for users from social networks.
	 * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($user_name, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $user_name)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
			$row->update(array(
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		}

		$userArr = $row->toArray();
		unset($userArr[self::COLUMN_PASSWORD_HASH]);

		$rolesArr = array();
		foreach( $row->related('users_acl_roles', 'users_id') as $role )
		{
			$rolesArr[] = $role->ref('acl_roles', 'acl_roles_id')->name;
		}

		return new Nette\Security\Identity($row[self::COLUMN_ID], $rolesArr, $userArr);
	}


	/**
 	 * @desc Do not use it for users from social networks
	 * @todo $params['roles'] are not implemented yet
	 * @param $params
	 * @return bool|int|Nette\Database\Table\IRow
	 * @throws App\Exceptions\DuplicateEntryException
	 * @throws \Exception
	 */
	public function add($params)
	{
		$params['roles'] = isset($params['roles']) ? $params['roles'] : array(3);
		$params['password'] = Passwords::hash($params['password']);


		$this->database->beginTransaction();
		try {
			$row = $this->database->table(self::TABLE_NAME)->insert(array(
						self::COLUMN_NAME => $params['user_name'],
						self::COLUMN_PASSWORD_HASH => $params['password'],
						self::COLUMN_EMAIL => $params['email'],
			));
		}
		catch(\PDOException $e)	{
			// This catch ONLY checks duplicate entry to fields with UNIQUE KEY
			$this->database->rollBack();
			$info = $e->errorInfo;

			// mysql==1062  sqlite==19  postgresql==23505
			if ($info[0] == 23000 && $info[1] == 1062)
			{
				// if/elseif returns the name of problematic field and value
				if( $this->database->table(self::TABLE_NAME)->where('user_name = ?', $params['user_name'])->fetch() )
				{
					$msg = 'user_name';
					$code = 1;
				}
				elseif( $this->database->table(self::TABLE_NAME)->where('email = ?', $params['email'])->fetch() )
				{
					$msg = 'email';
					$code = 2;
				}

				throw new App\Exceptions\DuplicateEntryException($msg, $code);

			}
			else { throw $e; }

		}

		try	{
			foreach ($params['roles'] as $role)
			{
				$this->database->table('users_acl_roles')->insert(array('users_id' => $row->id, 'acl_roles_id' => $role));
			}
		}
		catch(\Exception $e) {
			$this->database->rollBack();
			throw $e;
		}

		$this->database->commit();
		return $row;

	}

}
