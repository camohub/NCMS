<?php
namespace App\Model;

use Nette,
    Nette\Diagnostics\Debugger;


class BlogComments
{

    CONST
        TABLE_NAME = 'blog_comments';


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
     * @param bool $admin
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneBy($params, $admin = false)
    {
        $comments = $this->getTable()->where($params);
        return $comments->limit(1)->fetch();
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