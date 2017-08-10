<?php
/**
 * Config\Database
 * PHP version 7
 *
 * @category  Database
 * @package   Config
 * @author    Bill Rocha <prbr@ymail.com>
 * @copyright 2016 Bill Rocha <http://google.com/+BillRocha>
 * @license   <https://opensource.org/licenses/MIT> MIT
 * @version   GIT: 0.0.1
 * @link      http://paulorocha.tk/devbr
 */

namespace Config\Database;

/**
 * Config\Database Class
 *
 * @category Database
 * @package  Config
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Blog
{
    static $config = [
            'mysql'=>[
                'dsn'=>'mysql:host=localhost;dbname=devbr_site;charset=utf8',
                'user'=>'devbr_site',
                'passw'=>'devbr#123'],
            'sqlite'=>['dsn'=>'sqlite.db']
            ];
    static $default = 'mysql';

    //Configuração da tabela de usuário | Lib\User
    static $userTable = ['table'=>'usuario',
                         'id'=>'id',
                         'name'=>'nome',
                         'token'=>'token',
                         'life'=>'vida',
                         'login'=>'login',
                         'password'=>'senha',
                         'level'=>'nivel',
                         'status'=>'status'];
    
    /**
     * Get Database configurations
     *
     * @param string $alias Database config name
     *
     * @return bool|array    Array (or false) of the configurations
     */
    static function get($alias = null)
    {
        if ($alias === null) {
            return static::$config[static::$default];
        }
        if (isset(static::$config[$alias])) {
            return static::$config[$alias];
        } else {
            return false;
        }
    }

    /**
     * Get default database configuration
     *
     * @return string Alias of the default configurated database
     */
    static function getDefault()
    {
        return static::$default;
    }

    /**
     * Get user configuration
     *
     * @return array Array of user table configs.
     */
    static function getUserConfig()
    {
        return static::$userTable;
    }
}
