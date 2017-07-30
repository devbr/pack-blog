<?php
/**
 * Blog\Model\Admin
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

namespace Blog\Model\Reports;

use Lib;
use Lib\Db;

/**
 * Admin Class
 *
 * @category Model
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Report
{
    public $db = null;

    function __construct()
    {
        $this->db = new Db();
    }


    final function calculate($page = 1, $length = 10, $query, $data)
    {
        $tmp = $this->db->query($query, $data);

        if (!$tmp) {
            return false;
        }

        //Calculando ...
        $total = count($tmp);
        $sm = $total/$length;
        $int = intval($sm);
        $pages = $int < $sm ? $int + 1 : $int;

        //Limitando se requerir uma página maior ou menor que o limite.
        if ($page > $pages) {
            $page = $pages;
        }
        if ($page < 1) {
            $page = 1;
        }

        //registro inicial da página
        $init = ($page -1) * $length;

        return ['total'=>$total,
                'page'=>$page,
                'pages'=>$pages,
                'init'=>$init,
                'length'=>$length];
    }

    final function execute($data, $query, $dbdata = [])
    {
        $db = $this->db->query($query, $dbdata);

        if (!$db) {
            return false;
        }

        $rows = [];
        foreach ($db as $row) {
            $rows[] = $row->getAll();
        }

        $data['rows'] = $rows;
        return $data;
    }
}
