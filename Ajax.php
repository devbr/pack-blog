<?php
/**
 * Blog\Ajax
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
 * Ajax Class
 *
 * @category Controller
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Ajax
{

    public $patchHtml = __DIR__.'/Html/';
    private $articlePatch = 'media/article/';
    private $articleId = 0;


    /**
     * Hook test
     * @return string Data in POST
     */
    function put()
    {
        \App::e($_POST);
    }

    /**
     * Savar article
     * @return array|string return array with "status": "ok' OR "error"
     */
    function save()
    {
        $dt = '';
        if (isset($_POST['dt'])) {
            $dt = base64_decode($_POST['dt']);
            $dt = utf8_encode($dt);
            $dt = json_decode($dt);
        }

        if (isset($dt->content)) {
            $media =    isset($dt->media) ? $dt->media : '{}';
            $category = isset($dt->category) ? $dt->category : 1;
            $link =     isset($dt->link) ? $dt->link : '';
            $tags =     isset($dt->tags) ? $dt->tags : '';
            $status =   isset($dt->status) ? $dt->status : 'R';

            $title =    isset($dt->title) ? $dt->title : 'Sem Título';
            $destaque = isset($dt->destaque) ? $dt->destaque : 'Nenhum destaque ...';

            $uID = isset($dt->info->user) ? $dt->info->user : 0;
            $aID = isset($dt->info->article) ? $dt->info->article : 0;

            $article = new Model\Article($aID);

            $article->set('author', $uID);
            $article->set('category', $category);
            $article->set('pubdate', date('Y-m-d H:i:s'));
            $article->set('editdate', date('Y-m-d H:i:s'));

            $article->set('link', $link);
            $article->set('tags', $tags);
            $article->set('status', $status);
            $article->set('media', json_encode($media));
            
            $article->set('title', $title);
            $article->set('content', str_replace(["--", "..."], ["&mdash;", "&hellip;"], $dt->content));
            $article->set('resume', $destaque);

            //Gravando...
            $article->save();

            //Send to client
            $this->send(['status'=>'ok', 'id'=>$article->get('id'), 'link'=>$article->get('link')]);
        }
        $this->sendError();
    }


    /**
     * Check if "link" is "in use"
     * @return void Send data to javascript (Json)
     */
    function checkLink()
    {
        if (!isset($_POST['link'])) {
            $this->sendError();
        }

        $link = strtolower(str_replace([" ",'"',"'",';','.',','], ["-",""], preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities(trim($_POST['link'])))));

        $base = new Model\Base;
        $status = $base->checkLink($link, $_POST['aID']) === false ? 'ok' : 'error';

        $this->send(['status'=>$status,'link'=>$link]);
    }

    /**
     * Delete a article image (and thumbnail)
     * @param  string $r     URL requested
     * @param  array $param  article ID
     * @return void void...
     */
    function delete($r, $param)
    {
        $this->setUp($param);

        if (isset($_POST['file'])) {
            $file = _WWW.$this->articlePatch.basename(rawurldecode($_POST['file']));
            $mini = _WWW.$this->articlePatch.'mini_'.basename(rawurldecode($_POST['file']));

            if (file_exists($file)) {
                unlink($file);
            }
            if (file_exists($mini)) {
                unlink($mini);
            }
        }
    }

    /**
     * Upload image
     * @param  string $r     URL requested
     * @param  array $param  article ID
     * @return void Send data to client
     */
    function upload($r, $param)
    {
        $this->setUp($param);

        if (isset($_FILES['files']['error'][0])
            && $_FILES['files']['error'][0] == UPLOAD_ERR_OK) {
            $name = basename($_FILES["files"]["name"][0]);

            $ext = explode('.', $name);
            $ext = '.'.end($ext);

            $name = md5($name);

            $a['files'][0] = [
               'name' => $name.$ext,
               'size' => $_FILES['files']['size'][0],
               'type' => $_FILES['files']['type'][0],
               'url'  => _URL.$this->articlePatch.$name.$ext];

            //Create a directory (if not exists)
            \Devbr\Cli\Main::checkAndOrCreateDir(_WWW.$this->articlePatch, true);

            //Save uploaded file
            move_uploaded_file($_FILES["files"]["tmp_name"][0], _WWW.$this->articlePatch.$name.$ext);

            //Resize image
            $canvas = new \Devbr\Canvas(_WWW.$this->articlePatch.$name.$ext);
            $canvas->set_quality(80)
                   ->resize('540')
                   ->save(_WWW.$this->articlePatch.$name.$ext);
            //Thumbnail
            $canvas->set_rgb('#000')
                   ->set_quality(70)
                   //->resize('265', '150', 'fill')
                   ->resize('120', '68', 'fill')
                   ->save(_WWW.$this->articlePatch.'mini_'.$name.$ext);

            //Send to javascript
            $this->send($a);
        }

        header('HTTP/1.1 403 Forbidden');
        exit();
    }


// ------------------------- Privates

    private function setUp($param)
    {
        if (!isset($param['id'])) {
            $this->sendError();
        }

        $this->articleId = 0 + $param['id'];
        $this->articlePatch = $this->articlePatch.$this->articleId.'/';
    }


    /**
     * Send encoded json data
     * @return void Send data and stop PHP execution.
     */
    static function send($data)
    {
        @ob_end_clean();
        ob_start('ob_gzhandler');
        header('Vary: Accept-Language, Accept-Encoding');
        header('Content-Type: application/json');
        exit(json_encode($data));
    }

    static function sendError($data = false)
    {
        self::send(array_merge(['status'=>'error'], $data));
    }
}
