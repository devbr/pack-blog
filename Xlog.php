<?php
/**
 * Blog\Xlog
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

use Devbr\Aes;
use Devbr\Database;
use Devbr\User;
use Blog\Ajax;

/**
 * Xlog Class
 *
 * @category Controller
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Xlog
{

    function __construct()
    {
        #code here...
    }

    function key()
    {
        $key = str_replace(
            array("\r","\n","-----BEGIN PUBLIC KEY-----","-----END PUBLIC KEY-----"), '',
                            file_get_contents(_CONFIG.'Key/public.key'));
        Ajax::send(['key'=>$key]);
    }


    //Pega os dados do usuário por ajax encriptado com RSA
    //Retorna o0s dados encriptados por AES com o Token como Sincrono Key
    function signUp()
    {
        if (isset($_POST['data'])
            && trim($_POST['data']) !== '') {
            //Decodificando RSA
            $private = file_get_contents(_CONFIG.'Key/private.key');
            $key = json_decode($_POST['data']);
            $key = base64_decode($key->enc);

            if (!openssl_private_decrypt($key,
                                        $key,
                                        openssl_pkey_get_private($private)
                                        )) {
                Ajax::send(['error'=>'Confira seu Login ou Senha!']); //exit($key);
            }

            $key = json_decode($key);

            //inicializando o usuário => User Singleton Object
            $user = User::this();
            $user->login($key->login, $key->passw);


            //Verificando se o login foi bem sucedido
            if ($user->get('login')) {
                //Gravando o novo Token no BD
                $user->saveToken($key->token);
                $userdata = json_encode(['name'=>$user->get('name'),
                             'id'=>$user->get('id'),
                             'level'=>$user->get('level')]);

                $resumo = (new Model\Xlog)->resumo($user->get('id'));

                //Encriptando o token
                Aes::size(256);
                $key = Aes::enc(json_encode(
                                     ['user'=>$userdata,
                                      'token'=>$key->token,
                                      'resumo'=>$resumo]),
                                      $key->token);

                //Retorna os dados do usuário
                Ajax::send(['error'=>false, 'key'=>$key]);
            }
        }
        //Em casos contrários, retorna erro.
        Ajax::send(['error'=>'Confira seu Login ou Senha!']);
    }


    function logout()
    {
        //Procede o LOGOUT
        User::this()->logout($this->params[0]);
        Ajax::send(['error'=>false,'logout'=>'Você está desconectado!']);
    }


    /**
     * Escrevendo dados de acesso no banco de dados
     * @return void Não retorna
     */
    function access()
    {
        $data['agent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['method'] = $_SERVER['REQUEST_METHOD'];
        $data['uri'] = $_SERVER['REQUEST_URI'];
        $data['data'] = date('Y-m-d H:i:s');

        (new Model\Xlog)->setAccessData($data);
    }
}
