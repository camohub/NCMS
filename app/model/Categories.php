<?php
namespace App\Model;

use Nette,
    Nette\Diagnostics\Debugger;


class Categories
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
     * @return array
     * @desc returns array of arrays
     * like $a[0][5] $a[0][6] where [0] is parent_id which is array of his childrens
     */
    public function getArray()
    {
        $selection = $this->findAll();
        $arr = array();
        while($row = $selection->fetch())
        {
            $arr[$row['parent_id']][$row['id']] = $row;
        }

        return $arr;
    }



    /**
     * @return Nette\Database\Table\Selection
     */
    protected function findAll()
    {
        $selection = $this->getTable()
            ->where('visible = ?', 1)
            ->order('parent_id ASC, priority ASC');

        return $selection;
    }



    /**
     * @param $s
     * @return mixed
     */
    public function getCurrentSection($s)
    {
        $s = ltrim($s, ':');
        $s = explode(':', $s)[0];
        return $this->getTable()->where('url LIKE ?', '%'.$s.'%')->order('parent_id')->limit(1)->fetch();
    }


/////////Protected/Private////////////////////////////////////////////

    /**
     * @return Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        return $this->database->table('categories');
    }

}