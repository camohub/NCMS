<?php
namespace App\Model;

use Nette,
    App,
    Nette\Diagnostics\Debugger;


class Polls
{

    CONST
        TABLE_NAME = 'polls';


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
    public function getArray($admin = FALSE)
    {
        $selection = $this->findAll($admin);
        $arr = array();
        while($row = $selection->fetch())
        {
            $arr[$row['poll_id']][$row['id']] = $row;
        }

        return $arr;
    }



    /**
     * @param $id
     * @param bool $admin
     * @return Nette\Database\Table\Selection
     */
    public function findOnePoll($id, $admin = FALSE)
    {
        return $this->getTable()->where('id = ? OR poll_id = ?', $id, $id);
    }




    public function findAll($admin = FALSE, $order = NULL)
    {
        $polls = $this->getTable();
        $polls = $admin ? $polls : $polls->where('visible', 1);
        $polls = $order ? $order : $polls->order('id DESC');  // TODO today works only  if == NULL
        return $polls;
    }


    /**
     * @param $params
     * @param bool $admin
     * @return Nette\Database\Table\Selection
     */
    public function findBy($params, $admin = FALSE)
    {
        return $polls = $this->getTable()->where($params);
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
     * @param array $params
     * @param array $whereParams
     * @return int
     */
    public function update(Array $params, Array $whereParams)
    {
        return $this->getTable()->where($whereParams)->update($params);
    }


    /**
     * @param $id
     * @param bool $question
     * @return int|Nette\Database\Table\Selection
     */
    public function delete($id, $question = FALSE)
    {
        if(!$question)
        {
            return $this->getTable()->where('id = ? OR poll_id = ?', $id, $id)->delete();
        }
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