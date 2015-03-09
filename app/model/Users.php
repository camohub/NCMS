<?php
namespace App\Model;

use Nette,
    Nette\Diagnostics\Debugger;


class Users
{

    CONST   TABLE_NAME = 'users';


    /** @var Nette\Database\Context */
    protected $database;



    /**
     * @param Nette\Database\Context $db
     */
    public function __construct(Nette\Database\Context $db)
    {
        $this->database = $db;
    }



    /**
     * @return Nette\Database\Table\Selection
     */
    public function findAll($admin = NULL)
    {
        $users = $admin ? $this->getTable() : $this->getTable()->where('active', 1);
        return $users->order('user_name ASC');
    }


    /**
     * @param array $params
     * @param bool $admin
     * @return Nette\Database\Table\Selection
     */
    public function findOneBy(Array $params, $admin = FALSE)
    {
        $ndbt = $this->getTable()->where($params);
        $ndbt = $admin ? $ndbt : $ndbt->where('active = ?', 1);

        return $ndbt->fetch();
    }


//////////Protected/Private/////////////////////////////////////////////////

    /**
     * @return Nette\Database\Table\Selection
     */
    protected function getTable($table = NULL)
    {
        return $this->database->table($table ? $table : self::TABLE_NAME);
    }

}