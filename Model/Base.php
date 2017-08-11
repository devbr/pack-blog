<?php
/**
 * Blog\Model\Article
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
 * Article Class
 *
 * @category Model
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Base
{
    private $db = false;
    private $articleTable = 'article';
    private $contentTable = 'articlecontent';
    private $categoryTable = 'category';
    private $userTable = 'usuario';

    private $data = ['id'=>false,
                     'category' => false,
                     'author' =>false,
                     'pubdate' => false,
                     'editdate' => false,
                     'link' => false,
                     'tags' => false,
                     'status' => false,
                     'title' => false,
                     'media' => false,
                     'mediacaption' => false,
                     'resume' => false,
                     'content' => false
                     ];

    private $result = null;


    function __construct($id = null)
    {
        $this->db = new Db();
    }


    function getByLink($link)
    {
        $result = $this->db->query('SELECT *
                                      FROM '.$this->articleTable.', '.$this->contentTable.' 
                                      WHERE link LIKE :link
                                      AND article = id
                                      LIMIT 1', [':link'=>"%$link%"]);
        if (isset($result[0])) {
            return new Article($result[0]->getAll());
        }
            return false;
    }

    /**
     * Check link and if ID is diferent
     * @param  [type] $link [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    function checkLink($link, $id)
    {
        $result = $this->db->query('SELECT link 
                                      FROM '.$this->articleTable.' 
                                      WHERE link = :link 
                                      AND id != :id',

                              [':link'=>$link,
                               ':id'=>0+$id]);
        if (isset($result[0])) {
            return $result[0]->get('link');
        }
        
            $this->db->query('UPDATE '.$this->articleTable.'
                            SET link = :link
                            WHERE id = :id',

                    [':link'=>$link,
                     ':id'=>0+$id]);
            return false;
    }


    /**
     * Get categories
     * @return array category data
     */
    function getCategories()
    {
        $result = $this->db->query('SELECT * FROM category');

        $data = [];
        if (isset($result[0])) {
            foreach ($result as $v) {
                $data[$v->get('id')] = $v->get('name');
            }
        }
        return $data;
    }

    /**
     * Get Category by id
     * @param  integer $id Id for category
     * @return bool|array     Array of the name and description or false
     */
    function getCategory($id)
    {
        $result = $this->db->query('SELECT * FROM category WHERE id = :id', [':id'=>(0+$id)]);
        if (isset($result[0])) {
            return ['name'=>$result[0]->get('name'),
                'description'=>$result[0]->get('description')];
        }
        return false;
    }


    /**
     * Get status
     * @return array status data
     */
    function getStatus()
    {
        $result = $this->db->query('SELECT * FROM status');

        $data = [];
        if (isset($result[0])) {
            foreach ($result as $v) {
                $data[$v->get('id')] = $v->get('name');
            }
        }
        return $data;
    }


    /**
     * Create tables and insert user bases
     * @return void void
     */
    function create()
    {
        //Tablela ARTICLE
        $this->db->query("CREATE TABLE `article` (
                          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                          `category` int(11) NOT NULL DEFAULT '1',
                          `status` int(11) NOT NULL,
                          `author` int(11) NOT NULL DEFAULT '1',
                          `rateup` int(11) NOT NULL DEFAULT '0',
                          `ratedown` int(11) NOT NULL DEFAULT '0',
                          `access` int(11) NOT NULL DEFAULT '0',
                          `pubdate` datetime DEFAULT NULL,
                          `editdate` datetime DEFAULT NULL,
                          `link` varchar(300) DEFAULT NULL,
                          `tags` varchar(300) DEFAULT NULL,
                          `title` varchar(300) DEFAULT NULL,
                          `media` text,
                          `resume` text,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        //Tablela ARTICLECONTENT
            $this->db->query("CREATE TABLE `articlecontent` (
                          `article` int(10) unsigned NOT NULL,
                          `editdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
                          `content` longtext NOT NULL,
                          KEY `article` (`article`),
                          CONSTRAINT `articlecontent_ibfk_1` FOREIGN KEY (`article`) REFERENCES `article` (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        //Tablela CATEGORY
            $this->db->query("CREATE TABLE `category` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `name` varchar(200) NOT NULL,
                          `description` varchar(500) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

        //Tablela STATUS
            $this->db->query("CREATE TABLE `status` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `name` varchar(200) NOT NULL,
                          `description` varchar(500) NOT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

        //Tablela STATUS - INSERT
            $this->db->query("INSERT INTO `status` (`id`, `name`, `description`) VALUES
                        (1, 'Publicar imediatamente', 'Artigo publicado imediatamente depois de criado/editado.'),
                        (2, 'Salvar como Rascunho', 'Mantém como um rascunho para futura edição (estacionado)'),
                        (3, 'Deletar ou Desabilitar', 'Sinaliza que o artigo está deletado, porém, se mantém salvo para futura auditoria. '),
                        (4, 'Acesso Restrito - logado', 'Permite acesso somente a leitores logados.'),
                        (5, 'Editando...',  'O registro fica reservado para edição por 24 horas.'),
                        (6, 'Livre para reúso', 'Registro usado anteriormente, porém, livre para reúso.')");

        //Tablela USUARIO
            $this->db->query("CREATE TABLE `usuario` (
                          `id` int(11) NOT NULL AUTO_INCREMENT,
                          `login` varchar(255) DEFAULT NULL,
                          `senha` varchar(300) DEFAULT NULL,
                          `nome` varchar(255) DEFAULT NULL,
                          `token` varchar(200) DEFAULT NULL,
                          `vida` int(11) unsigned DEFAULT NULL,
                          `nivel` varchar(45) DEFAULT NULL COMMENT '[A]dmin, [E]ditor, [G]uest',
                          `status` char(1) DEFAULT NULL COMMENT '[A]ctive, [D]isable',
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        //Tablela USUARIO - INSERT
            $this->db->query("INSERT INTO `usuario` (`id`, `login`, `senha`, `nome`, `token`, `vida`, `nivel`, `status`) VALUES
                        (1, 'admin',  'admin#123',  'Administrator',  'DrFol:LeinC%[4CBVcT$hW<mdLEXE)vdCiZVV%Vq', 908,  'A',  'A'),
                        (2, 'editor', 'editor#123', 'Editor', 'v!BPf#hePX30Ks>GK~çjj',  908,  'E',  'A'),
                        (3, 'jessica',  'jessica#123',  'Jessica Mendes', '=+$Wr%yLEbxe7P11iWm1=d)Y@O%47Vow(2r{mv*u', 908,  'A',  'A'),
                        (5, 'guest',  'guest#123',  'Guest',  'IK:QernGB9azWQuh-A6BD',  0,  'G',  'A'),
                        (6, 'test', 'test#123', 'Disabled User',  '![vw*$3lP_z!#IVfe.#n8NiTKBE8<F*=40/B5@tq', 908,  'A',  'D'),
                        (7, 'evora',  'evora123', 'Evora Simpson Smith',  'sadkljhsad9sa04)(sadjhd',  908,  'A',  'A')");

        //Tablela USUARIOPAR - PARAMETROS DE USUÁRIOS
            $this->db->query("CREATE TABLE `usuariopar` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `usuario` int(11) NOT NULL,
                        `parametro` varchar(100) NOT NULL,
                        `valor` varchar(1000) NOT NULL,
                        PRIMARY KEY (`id`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Lista de parâmetros extras para cada usuário (opcional e varia entre usuários)'");

        //Tabela ACCESS
            $this->db->query("CREATE TABLE `access` (
                        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                        `data` datetime NOT NULL,
                        `ip` varchar(100) NOT NULL,
                        `uri` varchar(300) NOT NULL,
                        `method` varchar(20) NOT NULL,
                        `agent` varchar(500) NOT NULL,
                        PRIMARY KEY (`id`)
                      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }


// ------ oldies ¬

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function getResult()
    {
        return $this->result[0];
    }


    public function setId($id)
    {
        return $this->data['id'] = 0 + $id;
    }


    public function getId()
    {
        return $this->data['id'];
    }


    public function getData()
    {
        return $this->data;
    }


    public function get($node)
    {
        return isset($this->data[$node]) ? $this->data[$node] : false;
    }



    /**
     * Private util function
     *
     * @param  array $result Array of the Row object
     *
     * @return array result data
     */
    private function comList($result)
    {
        foreach ($result as $res) {
            $data[$res->get('id')]['title'] = $res->get('title');
            $data[$res->get('id')]['resume'] = $res->get('resume');
            $data[$res->get('id')]['author'] = $res->get('author');
            $data[$res->get('id')]['autor'] = $res->get('autor');
            $data[$res->get('id')]['category'] = $res->get('category');
            $data[$res->get('id')]['categoria'] = $res->get('categoria');
            $data[$res->get('id')]['link'] = $res->get('link');
            $data[$res->get('id')]['pubdate'] = $res->get('pubdate');
            //$data[$res->get('id')]['image'] = ['type'=>'picture', 'src'=>'/media/blog.png'];

            $media = json_decode($res->get('media'));

            foreach ($media as $img) {
                if (isset($img->type) && $img->type == 'image') {
                    $data[$res->get('id')]['image'] = '/media/article/'.$res->get('id').'/mini_'.basename($img->src);
                    break;
                }

                if (isset($img->type) && $img->type == 'video') {
                    $data[$res->get('id')]['image'] = '/media/v.png';
                }
            }
        }
    
        return $data;
    }

    public function searchIn($field, $text)
    {
        //Check if is field of $this->data
        if (!isset($this->data[$field])) {
            return false;
        }

        $result = $this->db->query('SELECT * 
                                      FROM '.$this->articleTable.', '.$this->contentTable.' 
                                      WHERE '.$field.' LIKE :tx
                                      AND article = id', [':tx'=>"%$text%"]);
        if (isset($result[0])) {
            if (count($result) > 1) {
                return $this->comList($result);
            } else {
                return $this->refresh($result[0]->getAll());
            }
        }
        return false;
    }

    /**
     * List ao article in this category
     *
     * @param  integer $cat category id
     *
     * @return array|bool data result or false
     */
    public function listByCategory($cat = null)
    {
        $result = $this->db->query('SELECT id, title, resume, category, author, link  
                                      FROM '.$this->articleTable.' 
                                      WHERE category = :cat', [':cat'=>$cat]);
        if (isset($result[0])) {
            return $this->comList($result);
        }
        return false;
    }


    /**
     * [listNews description]
     * @return [type] [description]
     */
    public function listNews($noID = 0, $limit = 5, $category = 0, $search = '')
    {
        $category = 0 + $category;
        $category = $category != 0 ? ' AND category='.$category : '';

        $src = trim($search);
        $search = $src != '' ? ' AND (title LIKE :src OR resume LIKE :src) ' : ' AND author != :src';

        $result = $this->db->query('SELECT article.id as id, article.title as title, resume, category, category.name as categoria, usuario.nome as autor, author, article.link as link, pubdate, media  
                                      FROM '.$this->articleTable.','.$this->categoryTable.','.$this->userTable.' 
                                      WHERE article.id != :id
                                      AND article.category = category.id
                                      AND article.author = usuario.id
                                      AND article.status = 1 
                                      '.$category.'
                                      '.$search.'
                                      ORDER BY pubdate DESC
                                      LIMIT '.$limit,

                              [':id'=> 0 + $noID, ':src'=>"%$src%"]);

        if (isset($result[0])) {
            return $this->comList($result);
        }
        return false;
    }

    /**
     * Refresh data
     *
     * @param  array $res new data
     *
     * @return void none
     */
    private function refresh($res)
    {
        foreach ($this->data as $k => $v) {
            $this->data[$k] = isset($res[$k]) ? $res[$k] : false;
        }
        return $this->data;
    }
}
