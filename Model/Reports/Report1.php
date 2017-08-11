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

use Devbr\Database as Db;

/**
 * Admin Class
 *
 * @category Model
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Report1 extends Report
{

    function view($page = 1, $length = 10, $year = false, $month = false, $day = false)
    {
        $db = $this->getData($page, $length, $year, $month, $day);
        
        $data['titulo'] = 'Acesso diário agrupado pela URL de acesso';

        if (!$db) {
            $data['content'] = '<p>Nenhum resultado.</p>';
            return $this->sendPage('admin', $data);
        }

        //mONTANDO ...
        $o = '';
        
        //Montando o resumo
        $s = $db['total'] > 1 ? 's':'';//Plural ??

        $sel = '<select id="length" onchange="send()">';
        for ($i = 5; $i <= $db['total']; $i += intval($db['total']/6)+1) {
            $sel .= '<option value="'.$i.'"'.($db['length'] == $i ? 'selected' : '').'>'.$i.'</option>';
        }
        $sel .= '</select>';
                
        $o .= "<p>Exibindo $sel de <b>$db[total]</b> registro$s por página.</p>";

        //Montando paginação
        $o .= '<p class="page">Página ';
        for ($i = 1; $i <= $db['pages']; $i++) {
            $o .= '<button type="button" onclick="sendBt('.$i.')"'.($db['page'] == $i ? ' class="active"':'').'>'.$i.'</button>';
        }
        $o .= '</p>';

        //Montando a TABELA
        $registros = 0;
        $o .= '<table><tr>';

        foreach ($db['rows'][0] as $key => $value) {
            $o .= "<th>$key</th>";
        }

        $o .= '</tr>';


        foreach ($db['rows'] as $key => $row) {
            $registros ++;
            $o .= '<tr>';

            foreach ($row as $k => $value) {
                if ($k == 'data') {
                    $o .= '<td>'.date('d/m/Y', strtotime($value)).'</td>';
                } else {
                    $o .= "<td>$value</td>";
                }
            }
            $o .= '</tr>';
        }

        $o .= '</table>';

        $data['content'] = $o;

        return $data;
    }

    /**
     * Quantidade de acesso ao HOME e ARTIGOS por dia do mês
     * @return [type] [description]
     */
    function getdata($page = 1, $length = 10, $ano = false, $mes = false, $dia = false)
    {
        
        $dt = [];
        $where = '';
        
        if ($ano !== false) {
            $where .= ' AND YEAR(data) = :ano ';
            $dt[':ano'] = $ano;
        }
        
        if ($mes !== false) {
            $where .= ' AND MONTH(data) = :mes ';
            $dt[':mes'] = $mes;
        }

        if ($dia !== false) {
            $where .= ' AND DAY(data) = :dia ';
            $dt[':dia'] = $dia;
        }

        //Calculando a página
        $data = $this->calculate($page, $length,
                                'SELECT id								 
								 FROM access								 
								 WHERE (uri LIKE "/a/%" 
								 OR uri = "/")
								 '.$where.'								 
								 GROUP BY uri, MONTH(data), DAY(data)', $dt);

        return $this->execute($data,
                                'SELECT data, COUNT(id)qt, uri AS Link 
								 FROM access
     							 WHERE (uri LIKE "/a/%" 
								 OR uri = "/")
								 '.$where.'
								 GROUP BY uri, MONTH(data), DAY(data)
								 ORDER BY DAY(data), uri
								 LIMIT '.$data['init'].', '.$data['length'], $dt);
    }
}
