<?php
/**
 * Blog\Page
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

//use Resource\Main;
use Devbr\Database;
use Devbr\User;
/**
 * Page Class
 *
 * @category Controller
 * @package  Library
 * @author   Bill Rocha <prbr@ymail.com>
 * @license  <https://opensource.org/licenses/MIT> MIT
 * @link     http://paulorocha.tk/devbr
 */
class Page
{

    public $scripts = ['blog/1'];
    public $styles = [];

    public $patchHtml = __DIR__.'/Html/';

    public $blogName = 'DBrasil';
    public $blogDescription = 'O jeito brasileiro de ver o mundo.';
    public $blogLink = _URL;
    public $blogArticleLink = _URL.'a/';
    public $blogMedia = _URL.'media/cover/destaque.jpg';

    public $header = false;
    public $footer = false;
    public $pageName = null;


    function index($rqst, $param)
    {
        //Data to template
        $data = [ 'blogName' => $this->blogName,
        'blogDescription' => $this->blogDescription,
        'blogLink' => $this->blogLink,
        'blogMedia' => $this->blogMedia
        ];
        //USER
        $user = User::this(); 
        $base = new Model\Base();

        $category = isset($_GET['category']) ? 0 + $_GET['category'] : 0;
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        $data['destaques'] = '';
        foreach ($base->listNews(0, 20, $category, $search) as $id => $row) {
            $data['destaques'] .= '<a href="'.$this->blogArticleLink.$row['link'].'"><div class="item">'.
            (isset($row['image']) ? '<img src="'._URL.trim($row['image'], "\\/ ").'">' : '').
            '<span class="pubdata"><b>'.strtoupper($row['categoria'].':</b> '.$row['autor']).' - '.date('d/m H:i', strtotime($row['pubdate'])).'</span>'.
            '<h2>'.$row['title'].'</h2>'.
            '<span class="resume">'.$row['resume'].'</span></div></a>';
        }

        if ($data['destaques'] == '') {
            $data['destaques'] = '<h2>Nenhuma publicação encontrada.</h2>';
        }

        //Categories
        $data['categories']['data'][0] = 'Tudo...';
        foreach ($base->getCategories() as $key => $value) {
            $data['categories']['data'][$key] = $value;
        }
        $data['categories']['default'] = $category;

        $data['search'] = $search;

        $this->sendPage('cover', $data);
    }


    /**
     * View article page
     *
     * @param string $rqst  [description]
     * @param array  $param [description]
     *
     * @return void        [description]
     */
    function view($rqst, $param)
    {
        if ($param['id'] === '0') {
            \App::go($this->blogLink);
        }

        $base =    new Model\Base();
        $article = new Model\Article($param['id']);

        if ($article->get('id') === false || $article->get('status') != 1) {
            \App::go($this->blogLink);
        }
        
        //Geting the first image in field "media" in database
        $media = json_decode($article->get('media'));
        $articleMedia = (isset($media[0]->src)) ? $media[0]->src : $this->blogMedia;

        //USER
        $user = User::this();
        $user->getById($article->get('author'));

        //Data to template
        $data = [ 'blogName' => $this->blogName,
        'blogDescription' => $article->get('resume').' - '.$this->blogDescription,
        'blogLink' => $this->blogLink,
        'blogMedia' => $this->blogMedia,

        'authorName' => $user->get('name'),
        'authorFoto' => _URL.'media/user/'.$user->get('id').'/1.jpg',
        'authorLink' => _URL.'perfil',  //'user/'.$user->get('login'),
        'authorPubData' => date("d/m/Y à\s H:i", strtotime($article->get('pubdate'))),

        'articleTitle' => $article->get('title'),
        'articleResume' => $article->get('resume'),
        'articleContent' => $article->get('content'),
        'articleMedia' => $articleMedia,
        'articleLink' => $this->blogArticleLink.$article->get('link'),
        'articleViews' => number_format($article->get('access'), 0, ',', '.'),
        'articleTags' => $article->get('tags'),
        'articleCategory' => $base->getCategory($article->get('category'))['name'],
        'articleDateTime' => date('c', strtotime($article->get('pubdate'))),
        'articleEditLink' => $this->blogLink.'e/'.$article->get('link')
        ];

        //Categories
        $data['categories']['data'][0] = 'Tudo...';
        foreach ($base->getCategories() as $key => $value) {
            $data['categories']['data'][$key] = $value;
        }
        
        //Lib\App::e($data);
        $this->sendPage('template', $data, ['aID'=>$article->get('id'),
                                'uID'=>$user->get('id')]);
    }


    function edit($rqst, $param)
    {
        //Impedindo que se acesse sem LOGIN...
        \App::go('login');

        //Checando se a rota está correta
        if (!isset($param['id'])) {
            \App::go($this->blogLink);
        }

        //USER
        $user = User::this();

        //$user->getMe();
        $user->getById(7);
        $aID = $param['id'] == 'new'
               || (is_numeric($param['id'])
                    && $param['id'] == 0)
               ? 0
               : $param['id'];
        
        $page = $aID == 0 ? 'new' : 'edit';

        $article = new Model\Article($aID);
        $base =    new Model\Base;

        $aID = $article->get('id') + 0;

        //Se não existir, cria um novo artigo.
        $data['blogName'] =             $this->blogName;
        $data['authorName'] =           $user->get('name');
        $data['authorFoto'] =           _URL.'media/user/'.$user->get('id').'/1.jpg';
        $data['authorLink'] =           _URL.'perfil'; //'user/'.$user->get('login');
        $data['authorPubData'] =        date("d/m/Y à\s H:i", strtotime($article->get('pubdate')));
        $data['articleTitle'] =         $article->get('title');
        $data['articleResume'] =        $article->get('resume');
        $data['articleContent'] =       $article->get('content');
        $data['articleLink'] =          $article->get('link');
        $data['articleViewLink'] =      $this->blogArticleLink.$article->get('link');

        //Select CATEGORIES
        $data['categoria']['data'] =    $base->getCategories();
        $data['categoria']['default'] = $article->get('category');

        //Select STATUS
        $data['status']['data'] =    $base->getStatus();
        $data['status']['default'] = $article->get('status');

        //Tags
        $data['articleTags'] = $article->get('tags');

        //$this->styles =  ['source/font-awesome.min'];
        $this->scripts = ['blog/2'];

        //Send page to user
        $this->sendPage('page_'.$page, $data, ['aType'=>$page, 'aID'=>$aID, 'uID'=>$user->get('id'), 'pageLink'=>$article->get('link')]);
    }


    function login()
    {
        //exit('<pre>'.print_r($_SERVER, true));
        //$this->styles = ['source/skell', 'source/login', 'source/login_doc'];
        $this->scripts = ['blog/3'];

        $key = str_replace(
            array("\r","\n","-----BEGIN PUBLIC KEY-----","-----END PUBLIC KEY-----"), '',
                            file_get_contents(\App::Config().'Key/public.key'));

        $this->sendPage('login', [], ['KEY'=>$key]);
    }


    function perfil()
    {
        //exit('<pre>'.print_r($_SERVER, true));
        //$this->styles = ['source/skell', 'source/login', 'source/login_doc'];
        $this->scripts = ['blog/3'];

        $this->sendPage('perfil');
    }


    /**
     * Utils
     * @param  [type] $page  [description]
     * @param  [type] $data  [description]
     * @param  [type] $jsvar [description]
     * @return [type]        [description]
     */
    final public function sendPage($page, $data = null, $jsvar = null)
    {
        $html = new \Devbr\Html();

        $html->setPathHtml($this->patchHtml)
             ->body($page)
             ->header($this->header)
             ->footer($this->footer)
             ->setName($this->pageName)
             ->insertScripts($this->scripts)
             ->insertStyles($this->styles)
             ->val($data)
             ->jsvar($jsvar)
             ->render()
             ->send();
    }



    //TEMP - de le te me
    
    function tmp()
    {
        //$xlog = new Model\Xlog;

        //$xlog->decodeAgent();

        //Fazendo login
        //Lib\User::this()->login('admin', 'admin#123');
        //Lib\User::this()->setCriptoCookie();

        //Lib\User::this()->unsetCriptoCookie();
        $user = new Lib\User();

        $user->login('jessica', 'jessica#123');

        //$user->unsetCriptoCookie();
        //
        //
        Lib\App::p($user->get(), true);
        Lib\App::p(Lib\User::this()->get(), true);
        Lib\App::p($_SERVER['REMOTE_ADDR'], true);
        Lib\App::p($_SERVER['HTTP_USER_AGENT'], true);
        Lib\App::p($_SERVER['HTTP_ACCEPT_LANGUAGE'], true);

        echo "<br>OS: </b>".$this->operating_system_detection();

        echo '<hr/>';

        $jsonBrowser = json_encode(get_browser());

        echo '<br><b>Tamanho do arquivo: </b>'.strlen($jsonBrowser).'<br>';
        Lib\App::p(json_decode($jsonBrowser), true);


        //Fazendo login
        //Lib\User::this()->login('jessica', 'jessica#123');
        //Lib\User::this()->setCriptoCookie();

        //Lib\App::p(Lib\User::this()->get(), true);

        echo "<hr/><b>Finished!</b>";
    }



    /* return Operating System */
    function operating_system_detection()
    {
        if (isset( $_SERVER )) {
            $agent = $_SERVER['HTTP_USER_AGENT'] ;
        } else {
            global $HTTP_SERVER_VARS ;
            if (isset( $HTTP_SERVER_VARS )) {
                $agent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'] ;
            } else {
                global $HTTP_USER_AGENT ;
                $agent = $HTTP_USER_AGENT ;
            }
        }
        $ros[] = array('Windows XP', 'Windows XP');
        $ros[] = array('Windows NT 5.1|Windows NT5.1)', 'Windows XP');
        $ros[] = array('Windows 2000', 'Windows 2000');
        $ros[] = array('Windows NT 5.0', 'Windows 2000');
        $ros[] = array('Windows NT 4.0|WinNT4.0', 'Windows NT');
        $ros[] = array('Windows NT 5.2', 'Windows Server 2003');
        $ros[] = array('Windows NT 6.0', 'Windows Vista');
        $ros[] = array('Windows NT 7.0', 'Windows 7');
        $ros[] = array('Windows NT 10.0', 'Windows 10');
        $ros[] = array('Windows CE', 'Windows CE');
        $ros[] = array('(media center pc).([0-9]{1,2}\.[0-9]{1,2})', 'Windows Media Center');
        $ros[] = array('(win)([0-9]{1,2}\.[0-9x]{1,2})', 'Windows');
        $ros[] = array('(win)([0-9]{2})', 'Windows');
        $ros[] = array('(windows)([0-9x]{2})', 'Windows');
    // Doesn't seem like these are necessary...not totally sure though..
    //$ros[] = array('(winnt)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'Windows NT');
    //$ros[] = array('(windows nt)(([0-9]{1,2}\.[0-9]{1,2}){0,1})', 'Windows NT'); // fix by bg
        $ros[] = array('Windows ME', 'Windows ME');
        $ros[] = array('Win 9x 4.90', 'Windows ME');
        $ros[] = array('Windows 98|Win98', 'Windows 98');
        $ros[] = array('Windows 95', 'Windows 95');
        $ros[] = array('(windows)([0-9]{1,2}\.[0-9]{1,2})', 'Windows');
        $ros[] = array('win32', 'Windows');
        $ros[] = array('(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})', 'Java');
        $ros[] = array('(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}', 'Solaris');
        $ros[] = array('dos x86', 'DOS');
        $ros[] = array('unix', 'Unix');
        $ros[] = array('Mac OS X', 'Mac OS X');
        $ros[] = array('Mac_PowerPC', 'Macintosh PowerPC');
        $ros[] = array('(mac|Macintosh)', 'Mac OS');
        $ros[] = array('(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'SunOS');
        $ros[] = array('(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}', 'BeOS');
        $ros[] = array('(risc os)([0-9]{1,2}\.[0-9]{1,2})', 'RISC OS');
        $ros[] = array('os/2', 'OS/2');
        $ros[] = array('freebsd', 'FreeBSD');
        $ros[] = array('openbsd', 'OpenBSD');
        $ros[] = array('netbsd', 'NetBSD');
        $ros[] = array('irix', 'IRIX');
        $ros[] = array('plan9', 'Plan9');
        $ros[] = array('osf', 'OSF');
        $ros[] = array('aix', 'AIX');
        $ros[] = array('GNU Hurd', 'GNU Hurd');
        $ros[] = array('(fedora)', 'Linux - Fedora');
        $ros[] = array('(kubuntu)', 'Linux - Kubuntu');
        $ros[] = array('(ubuntu)', 'Linux - Ubuntu');
        $ros[] = array('(debian)', 'Linux - Debian');
        $ros[] = array('(CentOS)', 'Linux - CentOS');
        $ros[] = array('(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)', 'Linux - Mandriva');
        $ros[] = array('(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)', 'Linux - SUSE');
        $ros[] = array('(Dropline)', 'Linux - Slackware (Dropline GNOME)');
        $ros[] = array('(ASPLinux)', 'Linux - ASPLinux');
        $ros[] = array('(Red Hat)', 'Linux - Red Hat');
    // Loads of Linux machines will be detected as unix.
    // Actually, all of the linux machines I've checked have the 'X11' in the User Agent.
    //$ros[] = array('X11', 'Unix');
        $ros[] = array('(linux)', 'Linux');
        $ros[] = array('(amigaos)([0-9]{1,2}\.[0-9]{1,2})', 'AmigaOS');
        $ros[] = array('amiga-aweb', 'AmigaOS');
        $ros[] = array('amiga', 'Amiga');
        $ros[] = array('AvantGo', 'PalmOS');
    //$ros[] = array('(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1}-([0-9]{1,2}) i([0-9]{1})86){1}', 'Linux');
    //$ros[] = array('(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1} i([0-9]{1}86)){1}', 'Linux');
    //$ros[] = array('(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1})', 'Linux');
        $ros[] = array('[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})', 'Linux');
        $ros[] = array('(webtv)/([0-9]{1,2}\.[0-9]{1,2})', 'WebTV');
        $ros[] = array('Dreamcast', 'Dreamcast OS');
        $ros[] = array('GetRight', 'Windows');
        $ros[] = array('go!zilla', 'Windows');
        $ros[] = array('gozilla', 'Windows');
        $ros[] = array('gulliver', 'Windows');
        $ros[] = array('ia archiver', 'Windows');
        $ros[] = array('NetPositive', 'Windows');
        $ros[] = array('mass downloader', 'Windows');
        $ros[] = array('microsoft', 'Windows');
        $ros[] = array('offline explorer', 'Windows');
        $ros[] = array('teleport', 'Windows');
        $ros[] = array('web downloader', 'Windows');
        $ros[] = array('webcapture', 'Windows');
        $ros[] = array('webcollage', 'Windows');
        $ros[] = array('webcopier', 'Windows');
        $ros[] = array('webstripper', 'Windows');
        $ros[] = array('webzip', 'Windows');
        $ros[] = array('wget', 'Windows');
        $ros[] = array('Java', 'Unknown');
        $ros[] = array('flashget', 'Windows');
    // delete next line if the script show not the right OS
    //$ros[] = array('(PHP)/([0-9]{1,2}.[0-9]{1,2})', 'PHP');
        $ros[] = array('MS FrontPage', 'Windows');
        $ros[] = array('(msproxy)/([0-9]{1,2}.[0-9]{1,2})', 'Windows');
        $ros[] = array('(msie)([0-9]{1,2}.[0-9]{1,2})', 'Windows');
        $ros[] = array('libwww-perl', 'Unix');
        $ros[] = array('UP.Browser', 'Windows CE');
        $ros[] = array('NetAnts', 'Windows');
        $file = count ( $ros );
        $os = '';
        for ($n=0; $n<$file; $n++) {
            if (preg_match('/'.$ros[$n][0].'/i', $agent, $name)) {
                $os = @$ros[$n][1].' '.@$name[2];
                break;
            }
        }
        return trim ( $os );
    }
}
