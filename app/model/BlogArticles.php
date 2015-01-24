<?php
namespace App\Model;

use Nette,
    Nette\Diagnostics\Debugger;


class BlogArticles
{

    CONST
        TABLE_NAME = 'blog_articles';


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
     * @param bool $admin
     * @param bool $order
     * @return Nette\Database\Table\Selection
     */
    public function findAll($admin = false, $order = true)
    {
        $articles = $admin ? $this->getTable() : $this->getTable()->where('status', 1);
        $articles = $order ? $articles->order('created ASC') : $articles;
        return $articles;
    }


    /**
     * @param $params
     * @param bool $admin
     * @param bool $order
     * @return Nette\Database\Table\Selection
     */
    public function findBy($params, $admin = false, $order = true)
    {
        $articles = $this->getTable()->where($params);
        $articles = $admin ? $articles : $articles->where('status', 1);
        $articles = $order ? $articles->order('created ASC') : $articles;
        return $articles;
    }


    /**
     * @param $params
     * @param bool $admin
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneBy($params, $admin = false)
    {
        $articles = $this->getTable()->where($params);
        $articles = $admin ? $articles : $articles->where('status', 1);
        return $articles->limit(1)->fetch();
    }



    /**
     * @param array $params
     * @return bool|int|Nette\Database\Table\IRow
     */
    public function insertComment(Array $params)
    {
        return $this->getTable('blog_comments')->insert($params);
    }



    /**
     * @param $params array
     * @return Nette\Database\Table\Selection
     *
    public function findForAdmin($params = array())
    {
        return $this->getTable()->where($params)->order('created ASC');
    }



    /**
     * @param $params array
     * @return Nette\Database\Table\Selection
     *
    public function findOneForAdmin($params)
    {
        return $this->getTable()->where($params)->order('created ASC')->fetch();
    }*/



    /**
     * @param $params
     * @return bool|int|Nette\Database\Table\IRow
     */
    public function insertArticle($params)
    {
        return $this->getTable()->insert($params);
    }


    /**
     * @param $params array
     * @param $id int
     * @return int
     */
    public function updateArticle($params, $id)
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