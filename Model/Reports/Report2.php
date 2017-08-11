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
class Report2 extends Report
{
    function view($page = 1, $length = 10)
    {
        $db = $this->getData($page, $length);
        
        $data['titulo'] = 'Artigos mais acessados';

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
    function getData($page = 1, $length = 10)
    {
        //Calculando a página
        $data = $this->calculate($page, $length,
                                'SELECT access.id
                                 FROM access, article, usuario, category
                                 WHERE uri LIKE "/a/%" 
                                 AND access.uri = CONCAT("/a/", article.link)
                                 AND article.author = usuario.id 
                                 AND article.category = category.id
                                 GROUP BY uri', []);

        return $this->execute($data, 'SELECT COUNT(access.id)Qt, article.title AS Titulo, usuario.nome AS Autor, category.name AS Categoria
                                    FROM access, article, usuario, category

                                    WHERE uri LIKE "/a/%" 
                                    AND access.uri = CONCAT("/a/", article.link)
                                    AND article.author = usuario.id 
                                    AND article.category = category.id

                                    GROUP BY uri
                                    ORDER BY Qt DESC

                                    LIMIT '.$data['init'].','.$data['length']);
    }
}
