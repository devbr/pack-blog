<?php
/**
 * Blog\Model\Xlog
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

use Lib;
use Lib\Db;

/**
 * Xlog Class
 *
 * @category Model
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Xlog
{
    private $db = false;
    private $accessTable = 'access';
    private $result = null;

    function __construct()
    {
        $this->db = new Db();
    }


    function setAccessData($data)
    {
        $set = ' SET ';
        foreach ($data as $k => $v) {
            $data[':'.$k] = $v;
            $set .= " $k = :$k,";
        }

        //tirando a última vírgula...
        $set = substr($set, 0, -1);

        //Escrevendo os dados no banco de dados
        $this->db->query('INSERT INTO '.$this->accessTable.' '.$set, $data);
    }

    function decodeAgent()
    {

        $result = $this->db->query('SHOW TABLE STATUS FROM devbr_site');
        \Lib\App::e($result);


        $result = $this->db->query('SELECT id,agent FROM '.$this->accessTable.' WHERE id > 6693');

        if (isset($result[0])) {
            $totalId = count($result);

            echo "\nEncontrados $totalId registros.";

            foreach ($result as $key => $value) {
                $dec = json_encode(get_browser($value->get('agent')));

                $this->db->query('UPDATE '.$this->accessTable.' SET decdata=:dec WHERE id=:id',
                    [':dec'=>$dec,
                     ':id'=>$value->get('id')]
                     );

                echo "\nId: ".$value->get('id')." de $totalId";
            }
        }

        exit("\n\nFinished!\n\n");
    }
}
