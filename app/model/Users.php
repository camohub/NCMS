<?php
namespace App\Model;

use Nette,
    Nette\Diagnostics\Debugger;


class Users
{

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
        $users = $admin ? $this->getTable() : $this->getTable()->where('visible', 1);
        return $users->order('user_name ASC');
    }


//////////Protected/Private/////////////////////////////////////////////////

    /**
     * @return Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        return $this->database->table('users');
    }

}