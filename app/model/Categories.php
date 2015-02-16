<?php
namespace App\Model;

use Nette,
    Nette\Diagnostics\Debugger;


class Categories
{

    CONST
    TABLE_NAME = 'categories';

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
     * like $a[0][5] $a[0][6] where [0] is parent_id which is array of his children
     */
    public function getArray($admin = false)
    {
        $selection = $this->findAll($admin);
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
    protected function findAll($admin = false)
    {
        $selection = $this->getTable();
        $selection = $admin ? $selection : $selection->where('visible = ?', 1);

        return $selection->order('parent_id ASC, priority ASC');
    }


    /**
     * @param array $params
     * @param bool $admin
     * @return Nette\Database\Table\Selection
     */
    public function findBy(Array $params, $admin = false)
    {
        $selection = $this->getTable()->where($params);
        $selection = $admin ? $selection : $selection->where('visible', 1);

        return $selection->order('parent_id ASC, priority ASC');
    }



    /**
     * @param $s string (:Admin:Blog....)
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByUrl($s)
    {
        $s = ltrim($s, ':');
        $s = explode(':', $s)[0];
        return $this->getTable()->where('url LIKE ?', '%' . $s . '%')->order('parent_id')->limit(1)->fetch();

    }


    /**
     * @param array $params
     * @param bool $admin
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneBy(Array $params, $admin = false)
    {
        $selection = $this->getTable()->where($params);
        $selection = $admin ? $selection : $selection->where('visible', 1);

        return $selection->fetch();
    }



    /**
     * @desc be carefull if parent does not exist returns same row
     * @param $id
     * @param bool $admin
     * @return Nette\Database\Table\IRow
     */
    public function getParentRow($id, $admin = false)
    {
        $row1 = $admin ? $this->getTable()->get((int)$id) : $this->getTable()->where(array('id' => (int)$id, 'visible' => 1))->fetch();
        $row2 = $this->getTable()->get($row1->parent_id);

        return $row2 ? $row2 : $row1;

    }



    /**
     * @param array $params
     * @return bool|int|Nette\Database\Table\IRow
     */
    public function add(Array $params)
    {
        return $this->getTable()->insert($params);
    }


    /**
     * @param $id
     * @param $params
     * @return int
     */
    public function update($id, $params)
    {
        return $this->getTable()->where('id', $id)->update($params);
    }


    /**
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        return $this->getTable()->where(array('id' => (int)$id, 'app' => 0))->delete();
    }


    /**
     * @return Nette\Database\Table\Selection
     */
    public function findModules()
    {
        return $this->getTable('modules');
    }



    /**
     * @param $params
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneModuleBy($params)
    {
        return $this->getTable('modules')->where($params)->fetch();
    }



//////Protected/Private///////////////////////////////////////////////////////

    /**
     * @return Nette\Database\Table\Selection
     */
    protected function getTable($tableName = NULL)
    {
        return $tableName ? $this->database->table($tableName) : $this->database->table(self::TABLE_NAME);
    }

}