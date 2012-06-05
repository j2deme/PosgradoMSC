<?php
header('Content-Type: text/html; charset=UTF-8');
require __DIR__.'/vendor/Slim/Slim.php';
require __DIR__.'/views/TwigView.php';
require __DIR__.'/vendor/ActiveRecord/ActiveRecord.php';
require __DIR__.'/vendor/Ladybug/Autoloader.php';
require __DIR__.'/vendor/GUMP/gump.class.php';
require __DIR__.'/vendor/Toolbox.php';
Ladybug\Autoloader::register();

$connections = array(
	'development' => 'mysql://root:root@localhost/taskmanager',
	'production' => 'mysql://'.getenv('MYSQL_USERNAME').':'.getenv('MYSQL_PASSWORD').'@'.getenv('MYSQL_DB_HOST').'/'.getenv('MYSQL_DB_NAME').';charset=utf8'
);
ActiveRecord\Config::initialize(function($cfg) use ($connections){
	$cfg->set_model_directory('models');
	$cfg->set_connections($connections); 
	$cfg->set_default_connection('development');
});


TwigView::$twigOptions = array('charset' => 'utf-8','strict_variables' => true);
TwigView::$twigDirectory = __DIR__.'/vendor/Twig/';
TwigView::$twigExtensions = array('Extension_Twig_Slim');

$app = new Slim(array(
    'debug' => true,
    'view' => 'TwigView'
));

$app->get('/', function () use($app) {
	/*$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$data['user'] = $user = User::find($userId);
	if($user->role != 1){
		$app -> redirect($app->urlFor('district', array('id' => $user->district)));	
	}*/
	$data['hello'] = "Hello World!";	
    $app->render('index.html', $data);
})->name('home');

$app->get('/login/', function () use($app) {
	$app->render('login.html');	
})->name('login');

$app->get('/logout/', function() use ($app) {
	$userId = $app -> getCookie('userId');
	if (isset($userId)) {
		$app -> setCookie('userId', null);
	}
	$app -> redirect($app->urlFor('login'));
})->name('logout');

$app->post('/login/', function() use ($app) {
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'username'    => 'required|alpha_numeric|max_len,100|min_len,5',
		'password'    => 'required|max_len,100|min_len,6',
	);

	$filters = array(
		'username' 	  => 'trim|sanitize_string',
		'password'	  => 'trim|md5'
	);
	$_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$user = User::find_by_username($_POST['username']);
		if(is_object($user)){
			if($user->password === $_POST['password']){
				$expiration = (isset($_POST['cookie']) ? '1 month' : '2 hours');
				$app -> setCookie('userId', $user -> id, $expiration);
				if($user->role == 1){
					$app -> redirect($app->urlFor('home'));	
				} else {
					$app -> redirect($app->urlFor('district', array('id' => $user->district)));
				}
				exit;	
			} else {
				//echo "Wrong Password!";
				$app -> redirect($app->urlFor('login'));
			}	
		} else {
			//echo "User does not exist";
			$app -> redirect($app->urlFor('login'));
		}
	} else {
		//echo "Campos requeridos";
		$app -> redirect($app->urlFor('login'));
	}
})->name('login-post');

$app->run();