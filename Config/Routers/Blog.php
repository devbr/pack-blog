<?php

	$router->respond('get', '/blog', 'Blog\Page::index')
	       ->respond('get', '/blog\?(.*)', 'Blog\Page::index')

	       ->respond('get', 'login', 'Blog\Page::login')
           ->respond('get', 'perfil', 'Blog\Page::perfil')

           ->respond('get', 'e/(?<id>(.*)?)', 'Blog\Page::edit')
           ->respond('get', 'a/(?<id>.*?)', 'Blog\Page::view')

           //AJAX ----------
           ->respond('post', 'x/put', 'Blog\Ajax::put')
           ->respond('post', 'x/save', 'Blog\Ajax::save')
           ->respond('post', 'x/checklink', 'Blog\Ajax::checkLink')
           ->respond('post', 'x/delete/(?<id>(\d+)?)', 'Blog\Ajax::delete')
           ->respond('post', 'x/upload/(?<id>(\d+)?)', 'Blog\Ajax::upload')

           //USER for Facebook
           ->respond('post', 'x/userlog', 'User\Facebook::userlog')

           //USERS
           ->respond('get', 'u/(?<id>(.*)?)', 'User\Facebook::perfil')

           //ADMIN
           ->respond('get', 'admin', 'Blog\Admin::index')
           ->respond('get', 'admin/(\d+)/(\d+)/(\d+)', 'Blog\Admin::pagination');