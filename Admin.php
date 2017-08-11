<?php
/**
 * Blog\Admin
 * PHP version 7
 *
 * @category  Controller
 * @package   Library
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.1
 * @link      http://paulorocha.tk/devbr
 */

namespace Blog;


/**
 * Admin Class
 *
 * @category Controller
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Admin
{
    public $scripts = [];
    public $styles = [];

    public $patchHtml = __DIR__.'/Html/';

    public $header = false;
    public $footer = false; 

    function __construct()
    {
        /* 
         * Your code here...
         *
         */
    }


    function index()
    {
        //return $this->view();
        $data['breadcumb'] = '<a href="'._URL.'">Home</a>';
        $data['titulo']  = 'Relatórios';
        $data['content'] = '<ul>
        						<li><a href="'._URL.'admin/1/1/10">Acesso diário agrupado pela URL de acesso</a></li>
        						<li><a href="'._URL.'admin/2/1/10">Artigos mais acessados</a></li>
        						<li><a href="'._URL.'admin/3/1/10">Acesso de Robots (agrupado por Agent)</a></li>
        					</ul>';
        $this->sendPage('admin', $data);
    }


    function pagination($r, $param)
    {
        $par0 = isset($param[0]) ? $param[0] : null;
        $par1 = isset($param[1]) ? $param[1] : null;
        $par2 = isset($param[2]) ? $param[2] : null;
        $par3 = isset($param[3]) ? $param[3] : null;

        switch ($par0) {
            case '1':
                $this->report1($par1, $par2);
                break;
            case '2':
                $this->report2($par1, $par2);
                break;
            case '3':
                $this->report3($par1, $par2);
                break;
            
            default:
                return $this->index();
                break;
        }
    }

    function report1($page = 1, $length = 10)
    {
        $model = new Model\Reports\Report1;
        $data = $model->view($page, $length);

        $data['baseUrl'] = 'http://dbrasil.tk/admin/1/';
        $data['breadcumb'] = '<a href="'._URL.'admin">Relatórios</a><a href="'._URL.'">Home</a>';

        $this->sendPage('admin', $data);
    }

    function report2($page = 1, $length = 10)
    {
        $model = new Model\Reports\Report2;
        $data = $model->view($page, $length);

        $data['baseUrl'] = 'http://dbrasil.tk/admin/2/';
        $data['breadcumb'] = '<a href="'._URL.'admin">Relatórios</a><a href="'._URL.'">Home</a>';

        $this->sendPage('admin', $data);
    }

    function report3($page = 1, $length = 10)
    {
        $model = new Model\Reports\Report3;
        $data = $model->view($page, $length);

        $data['baseUrl'] = 'http://dbrasil.tk/admin/3/';
        $data['breadcumb'] = '<a href="'._URL.'admin">Relatórios</a><a href="'._URL.'">Home</a>';

        $this->sendPage('admin', $data);
    }
}
