<?php
/**
 * Blog\Model\Result
 * PHP version 7
 *
 * @category  Model
 * @package   Library
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.1
 * @link      http://paulorocha.tk/devbr
 */

namespace Blog\Model;

use Devbr\Database as Db;

/**
 * Result Class
 *
 * @category Model
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Article
{
    private $table = 'article, articlecontent';
    private $where = 'article.id = articlecontent.article';
    private $patch = 'media/article/';

    private $id =           false;
    private $category =     1;
    private $status =       1;
    private $author =       1;
    private $rateup =       0;
    private $ratedown =     0;
    private $access =       0;
    private $pubdate =      '';
    private $editdate =     '';
    private $link =         'titulo-da-publicaçao';
    private $tags =         '';
    private $title =        'Titulo da Publicação';
    private $media =        '"{}"';
    private $resume =       'Digite um texto para destaque e descrição resumida da publicação';
    private $content =      'Conteúdo da publicação...';

    function __construct($data = null)
    {
        if ($data === null) {
            return;
        }

        if ($data === 0) {
            $this->requestNew();
        }
        
        if (is_array($data)) {
            $this->load($data);
        }
        if (is_numeric($data)) {
            $this->mountById($data);
        }

        if (is_string($data)) {
            $this->mountByLink($data);
        }
    }

    /**
     * Mount from DataBase - search by ID
     * @param  integer $id id in database
     * @return bool|object $this object or false
     */
    function mountById($id)
    {
        $db = new Db();

        if ($this->where != false || $this->where != '') {
            $where = ' AND '.$this->where;
        }

        $res = $db->query('SELECT * FROM '.$this->table.' WHERE id = :id '.$where, [':id'=>0 + $id]);

        if (isset($res[0])) {
            $this->load($res[0]->getAll());

            //Access counter
            $db->query('UPDATE article
                           SET access = access + 1                               
                         WHERE id = '.$this->id);
            return $this;
        }
        return false;
    }


    /**
     * Mount from DataBase - search by LINK
     * @param  integer $link link in database
     * @return bool|object $this object or false
     */
    function mountByLink($link)
    {
        $db = new Db();

        if ($this->where != false || $this->where != '') {
            $where = ' and '.$this->where;
        }
        $res = $db->query('SELECT * FROM '.$this->table.' WHERE link = :link '.$where, [':link'=>$link]);

        if (isset($res[0])) {
            $this->load($res[0]->getAll());
    
            //Access counter
            $db->query('UPDATE article
                           SET access = access + 1                               
                         WHERE id = '.$this->id);
            return $this;
        }
        
        return false;
    }


    /**
     * Get Row in DB
     *
     * @return bool|integer New ID or false
     */
    function requestNew()
    {
        $db = new Db;

        //Search from first register with [article.status] 5 and [article.editdate] <= datetime - 24 hours.
        $result = $db->query('SELECT MIN(id)id, (SELECT MAX(id+1) FROM article)nid
                              FROM article
                              WHERE status = 6
                              OR (status = 5 AND editdate <=  STR_TO_DATE(\''.date('Y-m-d H:i:s', time()-86400).'\', \'%Y-%m-%d %H:%i:%s\'))');

        if (isset($result[0])) {
            $this->editdate = date('Y-m-d H:i:s');
            $this->pubdate = $this->editdate;


            //Criando novo registro
            if ($result[0]->get('id') == null) {
                $this->id = $result[0]->get('nid');

                $db->query('INSERT INTO articlecontent
                           SET article = '.$this->id.',
                           	   content = "",
            				   editdate = "'.$this->editdate.'"');

                $db->query('INSERT INTO article
                           SET id = '.$this->id.',
                               status = 5,
                               editdate = \''.$this->editdate.'\'');

            //Atualizando o registro
            } else {
                $this->id = $result[0]->get('id');

                $db->query('UPDATE article
                           SET status = 5,
                               pubdate = \''.$this->pubdate.'\',
            				   editdate = \''.$this->editdate.'\',
            				   rateup = 0,
            				   ratedown = 0,
            				   access = 0,
            				   link = "",
            				   tags = "",
            				   title = "",
            				   media = "{}",
            				   resume = ""
            				   
            			 WHERE id = '.$this->id.'');

                $db->query('UPDATE articlecontent
                           SET content = "",
            				   editdate = \''.$this->editdate.'\'
            			 WHERE article = '.$this->id.'');
            }

                //Make dir clean (delete all files)
                $this->clearDir();
                return $this;
        }

            return false;
    }

    /**
     * Get All data
     * @return array array of fields name X data
     */
    function getAll()
    {
        foreach ($this as $k => $v) {
            if ($k == 'table' || $k == 'where') {
                continue;
            }
            $data[$k] = $v;
        }
        return $data;
    }

    /**
     * Get one item
     * @param  string $item Item
     * @return bool|string requaired item ou false
     */
    function get($item)
    {
        if (isset($this->$item)) {
            return $this->$item;
        }
        return false;
    }

    /**
     * Set one item
     * @param string $item  Item name
     * @param string $value Value
     *
     * @return bool|object $this object or false
     */
    function set($item, $value)
    {
        if (isset($this->$item)) {
            $this->$item = $value;
            return $this;
        }
        return false;
    }


    /**
     * SAVE a new or UPDATE this
     * @return bool status of success
     */
    function save()
    {
        if ($this->id != false || $this->id != 0) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * DELETE this
     * @return bool status of success
     */
    function delete()
    {
        if ($this->id <= 0) {
            return false;
        }

        $db = new Db();
        $dlt = $db->query('DELETE FROM articlecontent WHERE article = :id', [':id'=>$this->id]);
        $dlt = $db->query('DELETE FROM article WHERE id = :id', [':id'=>$this->id]);

        return $dlt;
    }


    // Privates ---------------------------------------------
    
    /**
     * Delete all files in article directory
     * @return void Clear directory
     */
    function clearDir()
    {
        $dir = _WWW.$this->patch.$this->id.'/';
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            unlink($dir.$file);
        }
    }
    
    
    /**
     * [load description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    function load($data = [])
    {
        foreach ($this as $k => $v) {
            if ($k == 'table' || $k == 'where') {
                continue;
            }
            if (isset($data[$k])) {
                $this->$k = $data[$k];
            }
        }
    }

    /**
     * [update description]
     * @return [type] [description]
     */
    private function update()
    {
        $db = new Db();

        //Insert into article table
        $row = $db->query('UPDATE article
                            SET   category = :category,
                                  author = :author,
                                  pubdate = :pubdate,
                                  editdate = :editdate,
                                  tags = :tags,
                                  status = :status,
                                  title = :title,
                                  media = :media,
                                  resume = :resume
                            WHERE id = :id',

                        [':id'=>$this->id,
                         ':category' => $this->category,
                         ':author' => $this->author,
                         ':pubdate' => $this->pubdate,
                         ':editdate' => $this->editdate,
                         ':tags' => $this->tags,
                         ':status' => $this->status,
                         ':title' => $this->title,
                         ':media' => $this->media,
                         ':resume' => $this->resume
                        ]);
        //check update
        if (!$row) {
            return false;
        }

        //Insert into content table
        $row = $db->query('UPDATE articlecontent
                            SET   editdate = :editdate,
                                  content = :content
                            WHERE article = :article',
                            
                        [':article' => $this->id,
                         ':editdate' => $this->editdate,
                         ':content' => $this->content]);
        return $row;

        
        $cols = '';
        $vals = [];
        foreach ($this as $k => $v) {
            if ($k == 'table' || $k == 'where') {
                continue;
            }echo '<br><b>'.$k.': </b>'.$v;
            if ($k !== 'id') {
                $cols .= $k.' = :'.$k.',';
            }
            $vals[':'.$k] = $v;
        }
        
        $cols = substr($cols, 0, -1); //tirando a ultima vírgula

        $where = ' WHERE id = :id ';
        if ($this->where != null) {
            $where .= ' and '.$this->where;
        }

        $db = new Db();
        return $db->query('UPDATE '.$this->table.' SET '.$cols.$where, $vals);
    }

    /**
     * [insert description]
     * @return [type] [description]
     */
    private function insert()
    {
        $db = new Db();

        //Pegando um ID válido
        $id = $db->query('SELECT MAX(id)id FROM article');
        $this->id = $id[0]->get('id')+1;

                //Insert into content table
        $row = $db->query('INSERT INTO articlecontent
                            SET article = :article,
                                editdate = :editdate,
                                content = :content',
                        [':article' => $this->id,
                         ':editdate' => $this->editdate,
                         ':content' => $this->content]);

        //check insert
        if (!$row) {
            //TODO: insert a ROWBACK if not inserted.

            return false;
        }
        
        //Insert into article table
        $row = $db->query('INSERT INTO article
                            SET id = :id,
                                category = :category,
                                author = :author,
                                pubdate = :pubdate,
                                editdate = :editdate,
                                link = :link,
                                tags = :tags,
                                status = :status,
                                title = :title,
                                media = :media,
                                resume = :resume ',
                        [':id'=>$this->id,
                         ':category' => $this->category,
                         ':author' => $this->author,
                         ':pubdate' => $this->pubdate,
                         ':editdate' => $this->editdate,
                         ':link' => $this->link,
                         ':tags' => $this->tags,
                         ':status' => $this->status,
                         ':title' => $this->title,
                         ':media' => $this->media,
                         ':resume' => $this->resume
                        ]);

        return $row;
    }
}
