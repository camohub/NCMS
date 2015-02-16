<?php
namespace App\Model;

use Nette,
    App,
    Nette\Diagnostics\Debugger;


class Images
{

    CONST
        TABLE_NAME = 'images';


    /** @var Nette\Database\Context */
    protected $database;



    /**
     * @param Nette\Database\Context $db
     */
    public function __construct(Nette\Database\Context $db)
    {
        $this->database = $db;
    }



    public function findAll($admin = false, $order = NULL)
    {
        $imgs = $this->getTable();
        $imgs = $order ? $order : $imgs->order('id DESC');  // TODO today works only  if == NULL
        return $imgs;
    }


    /**
     * @param $params
     * @param bool $admin
     * @return Nette\Database\Table\Selection
     */
    public function findBy($params, $admin = false)
    {
        return $comments = $this->getTable()->where($params);
    }


    /**
     * @param $params
     * @return mixed
     */
    public function findOneBy($params)
    {
        $comments = $this->getTable()->where($params);
        return $comments->limit(1)->fetch();
    }


    /**
     * @param $name
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByName($name)
    {
        $comments = $this->getTable()->where('name LIKE ?', $name);
        return $comments->limit(1)->fetch();
    }


    /**
     * @param array $params
     * @return bool|int|Nette\Database\Table\IRow
     * @throws App\Exceptions\DuplicateEntryException
     */
    public function insert(Array $params)
    {
        try {
            return $this->getTable()->insert($params);
        }
        catch(\PDOException $e) {
            // This catch ONLY checks duplicate entry to fields with UNIQUE KEY
            $info = $e->errorInfo;

            // mysql==1062  sqlite==19  postgresql==23505
            if ($info[0] == 23000 && $info[1] == 1062) {
                throw new App\Exceptions\DuplicateEntryException($e->getMessage(), 1);
            } else {
                throw $e;
            }
        }
    }



    /**
     * @param $params array
     * @param $id int
     * @return int
     */
    public function update($params, $id)
    {
        return $this->getTable()->where('id', $id)->update($params);
    }


    /**
     * @param $id
     * @return int
     */
    public function delete($id)
    {
        return $this->getTable()->where('id', $id)->delete();
    }


////Protected/Private//////////////////////////////////////////////////////

    /**
     * @param null $table
     * @return Nette\Database\Table\Selection
     */
    protected function getTable($table = NULL)
    {
        return $this->database->table($table ? $table : self::TABLE_NAME);
    }

}