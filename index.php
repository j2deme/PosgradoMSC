<?php
header('Content-Type: text/html; charset=UTF-8');
require __DIR__.'/vendor/Slim/Slim.php';
require __DIR__.'/views/TwigView.php';
require __DIR__.'/vendor/ActiveRecord/ActiveRecord.php';
require __DIR__.'/vendor/Ladybug/Autoloader.php';
require __DIR__.'/vendor/GUMP/gump.class.php';
require __DIR__.'/vendor/Toolbox.php';
Ladybug\Autoloader::register();

/*$connections = array(
   'development' => 'mysql://username:password@localhost/development',
   'pgsql' => 'pgsql://username:password@localhost/development'
 );*/
//;charset=utf8
$connections = array(
	'development' => 'mysql://root:root@localhost/taskmanager',
	'production' => 'mysql://'.getenv('MYSQL_USERNAME').':'.getenv('MYSQL_PASSWORD').'@'.getenv('MYSQL_DB_HOST').'/'.getenv('MYSQL_DB_NAME').';charset=utf8'
);
# must issue a "use" statement in your closure if passing variables
ActiveRecord\Config::initialize(function($cfg) use ($connections){
	$cfg->set_model_directory('models');
	$cfg->set_connections($connections); 
	$cfg->set_default_connection('production');
});


TwigView::$twigOptions = array(
	'charset' => 'utf-8',
	'strict_variables' => true
);
TwigView::$twigDirectory = __DIR__.'/vendor/Twig/';
TwigView::$twigExtensions = array(
    'Extension_Twig_Slim'
);

$app = new Slim(array(
    'debug' => true,
    'templates.path' => './templates',
    'view' => 'TwigView'
));

$app->get('/', function () use($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$data['user'] = $user = User::find($userId);
	if($user->role != 1){
		$app -> redirect($app->urlFor('district', array('id' => $user->district)));	
	}
	$districts = array();
	$districts[] = District::find(5);
	$districts[] = District::find(4);
	$districts[] = District::find(3);
	$districts[] = District::find(2);
	$districts[] = District::find(1);
	$districts[] = District::find(8);
	$districts[] = District::find(7);
	$districts[] = District::find(6);
	
	$data['districts'] = $districts;
	$data['disis'] = array();
	foreach ($data['districts'] as $district) {
		$disi = new Generic();
		$disi->id = $district->id;
		$disi->status = "";
		$assignations = Assignation::find_all_by_district($district->id);
		$status['finished'] = $status['started'] = $status['not_started'] = 0;
		foreach ($assignations as $assignation) {
			if($assignation->status == 0){
				$status['not_started']++;
			} elseif ($assignation->status > 0 && $assignation->status < 2) {
				$status['started']++;
			} else {
				$status['finished']++;
			}			
		}
		if($status['not_started'] > 0){
			$disi->status = "not-started";
		} elseif($status['started'] > 0) {
			$disi->status = "started";
		} else {
			$disi->status = "finished";
		}
		$data['disis'][$disi->id] = $disi;
	}
	
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

$app->get('/d:id/', function($id) use ($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$data['user'] = User::find($userId);
	$data['district'] = District::find($id);
	$data['activities'] = $data['district']->activities;
	$data['actis'] = array();
	foreach ($data['activities'] as $activity) {
		$acti = new Generic();
		$acti->id = $activity->id;
		$acti->status = "";
		$assignations = Assignation::find_all_by_district_and_activity($id,$activity->id);
		$status['finished'] = $status['started'] = $status['not_started'] = 0;
		foreach ($assignations as $assignation) {
			if($assignation->status == 0){
				$status['not_started']++;
			//} elseif ($assignation->status > 0 && $assignation->status < 10) {
			} elseif ($assignation->status > 0 && $assignation->status < 2) {
				$status['started']++;
			} else {
				$status['finished']++;
			}			
		}
		if($status['not_started'] > 0){
			$acti->status = "not-started";
		} elseif($status['started'] > 0) {
			$acti->status = "started";
		} else {
			$acti->status = "finished";
		}
		$data['actis'][$acti->id] = $acti;
	}
	$app->render('district.html',$data);
})->name('district');

$app->get('/d:district/a:activity/', function($district_id,$activity_id) use ($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$data['user'] = User::find($userId);
	$data['district'] = District::find($district_id);
	$data['activity'] = Activity::find($activity_id);
	$data['tasks'] = $data['activity']->tasks;
	$data['taskis'] = array();
	foreach ($data['tasks'] as $task) {
		$taski = new Generic();
		$taski->id = $task->id;
		$taski->status = "";
		$assignations = Assignation::find_all_by_district_and_activity_and_task($district_id,$activity_id,$task->id);
		$status['finished'] = $status['started'] = $status['not_started'] = 0;
		foreach ($assignations as $assignation) {
			if($assignation->status == 0){
				$status['not_started']++;
			//} elseif ($assignation->status > 0 && $assignation->status < 10) {
			} elseif ($assignation->status > 0 && $assignation->status < 2) {
				$status['started']++;
			} else {
				$status['finished']++;
			}			
		}
		if($status['not_started'] > 0){
			$taski->status = "not-started";
		} elseif($status['started'] > 0) {
			$taski->status = "started";
		} else {
			$taski->status = "finished";
		}
		$data['taskis'][$taski->id] = $taski;
	}
	$app->render('activity.html',$data);
})->name('activity');

$app->get('/d:district/a:activity/t:task/', function($district_id,$activity_id,$task_id) use ($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$data['user'] = User::find($userId);
	$data['district'] = District::find($district_id);
	$data['activity'] = Activity::find($activity_id);
	$data['task'] = Task::find($task_id);
	$data['assignation'] = $assignation = Assignation::find_by_district_and_activity_and_task($district_id,$activity_id,$task_id); 
	$data['subtasks'] = $data['assignation']->subtasks;
	$data['subis'] = array();
	foreach ($data['subtasks'] as $subtask) {
		$subi = new Generic();
		$subi->status = "";
		if($subtask->progress == 0){
			$subi->visual = "No iniciada";
			$subi->status = "not-started";
		} elseif($subtask->progress == 1) {
			$subi->visual = "En progreso";
			$subi->status = "started";
		} else {
			$subi->visual = "Finalizada";
			$subi->status = "finished";
		}
		$data['subis'][$subtask->id] = $subi;
		$data['comments'][$subtask->id] = $subtask->comments;
		$data['cmts'] = array();
		foreach ($subtask->comments as $comment){
			$cmti = new Generic();
			$cmti->status = "";
			if($comment->status == 0){
				$cmti->visual = "No iniciada";
			} elseif($comment->status == 1) {
				$cmti->visual = "En progreso";
			} else {
				$cmti->visual = "Finalizada";
			}
			$data['cmtis'][$comment->id] = $cmti;
		}
	}
	$app->render('task.html',$data);
})->name('task');

$app->get('/d:district/a:activity/t:task/sb/', function($district_id,$activity_id,$task_id) use ($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$data['user'] = User::find($userId);
	$data['district'] = District::find($district_id);
	$data['activity'] = Activity::find($activity_id);
	$data['task'] = Task::find($task_id); 
	$app->render('subtask.html',$data);
})->name('subtask');

$app->post('/d:district/a:activity/t:task/sb/', function($district_id,$activity_id,$task_id) use ($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'name'		=> 'required',
		'description'	=> 'required'
	);

	$filters = array(
		'name' 	  	=> 'trim',
		'description'	=> 'trim',
		'start-date'	=> 'trim',
		'end-date'		=> 'trim'
	);
	$_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
	if($validated === TRUE) {
		$assignation = Assignation::find_by_district_and_activity_and_task($district_id,$activity_id,$task_id);
		$subtask = new Subtask();
		$subtask->name = $_POST['name'];
		$subtask->description = $_POST['description'];
		$start = $_POST['start-date']; 
		list($day, $month, $year) = explode('/', $start); 
		$timestamp = mktime(0, 0, 0, $month, $day, $year); 
		$subtask->start = $timestamp;
		$end = $_POST['end-date']; 
		list($day, $month, $year) = explode('/', $end); 
		$timestamp = mktime(0, 0, 0, $month, $day, $year); 
		$subtask->end = $timestamp;
		$subtask->progress = 0;
		$subtask->assignation = $assignation->id;
		$subtask->save();
				
		update_assignation_status($assignation->id);
		$app->redirect($app->urlFor('task', array('district' => $district_id, 'activity' => $activity_id, 'task' => $task_id)));
	} else {
		$app->redirect($app->urlFor('subtask', array('district' => $district_id, 'activity' => $activity_id, 'task' => $task_id)));
	}
})->name('subtask-post');

$app->get('/d:district/a:activity/t:task/sb/e/:sb/', function($district_id,$activity_id,$task_id,$subtask_id) use ($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	$data['user'] = User::find($userId);
	$data['district'] = District::find($district_id);
	$data['activity'] = Activity::find($activity_id);
	$data['task'] = Task::find($task_id);
	$data['subtask'] = Subtask::find($subtask_id);  
	$app->render('subtask-edit.html',$data);
	
})->name('subtask-edit');

$app->post('/d:district/a:activity/t:task/sb/e/:sb/', function($district_id,$activity_id,$task_id,$subtask_id) use ($app) {
	$userId = $app -> getCookie('userId');
	if(!isset($userId)) {
		$app -> redirect($app->urlFor('login'));
	}
	ladybug_dump($_POST);
	$validator = new GUMP();
	$_POST = $validator->sanitize($_POST);
	$rules = array(
		'name'		=> 'required',
		'description'	=> 'required',
		'comment'	=> 'required|max_len,100'
	);

	$filters = array(
		'name' 	  	=> 'trim',
		'description'	=> 'trim',
		'start-date'	=> 'trim',
		'end-date'		=> 'trim',
		'comment' 	=> 'trim'
	);
	$_POST = $validator->filter($_POST, $filters);
	$validated = $validator->validate($_POST, $rules);
#	$_POST['name'] = utf8_decode($_POST['name']);
#	ladybug_dump($_POST);
	if($validated === TRUE) {
		$assignation = Assignation::find_by_district_and_activity_and_task($district_id,$activity_id,$task_id);
		$subtask = Subtask::find($subtask_id);
		$subtask->name = $_POST['name'];
		$subtask->description = $_POST['description'];
		$start = $_POST['start-date']; 
		list($day, $month, $year) = explode('/', $start); 
		$timestamp = mktime(0, 0, 0, $month, $day, $year); 
		$subtask->start = $timestamp;
		$end = $_POST['end-date']; 
		list($day, $month, $year) = explode('/', $end); 
		$timestamp = mktime(0, 0, 0, $month, $day, $year); 
		$subtask->end = $timestamp;
		$subtask->progress = $_POST['progress'];
		$subtask->assignation = $assignation->id;
		$subtask->save();
		
		$sb = Subtask::find('last');
		$comment = new Comment();
		$comment->subtask = $subtask_id;
		$comment->status = $subtask->progress;
		$comment->user = $userId;
		$comment->created = time();
		$comment->body = $_POST['comment'];
		$comment->save();
		
		update_assignation_status($assignation->id);
		$app->redirect($app->urlFor('task', array('district' => $district_id, 'activity' => $activity_id, 'task' => $task_id)));
	} else {
		$app->redirect($app->urlFor('subtask-edit', array('district' => $district_id, 'activity' => $activity_id, 'task' => $task_id, 'sb' => $subtask_id)));
	}
})->name('subtask-edit-post');

$app->post('/sb/d/:sb/', function($id) use ($app) {
# ALTER TABLE  `subtasks` ADD  `deleted` INT( 11 ) NOT NULL DEFAULT  '0' 
	$subtask = Subtask::find($id);
	if(is_object($subtask)){
		$assignation = Assignation::find($subtask->assignation);	
		/*$comments = Comment::find_all_by_subtask($subtask->id);
		foreach ($comments as $comment) {
			$comment->delete();
			unset($comment);
		}
		$subtask->delete();*/
		$subtask->deleted = 1;
		$subtask->save();
		update_assignation_status($assignation->id);
		$app->redirect($app->urlFor('task', array('district' => $assignation->district, 'activity' => $assignation->activity, 'task' => $assignation->task)));	
	} else {
		$app->redirect('home');
	}
})->name('subtask-delete-post');

$app->get('/load/', function () use ($app) {
	/*$tb = new Toolbox();
	$passwords = array();
	$users = array(
		array('username'=> 'Administrador1', 'district'=> 0, 'role' => 1),
		array('username'=> 'Administrador2', 'district'=> 0, 'role' => 1),
		array('username'=> 'Administrador3', 'district'=> 0, 'role' => 1),
	);
	
	for ($i=1; $i <= 8; $i++) { 
		$users[] = array('username'=> "Supervisor$i", 'district'=> $i, 'role' => 2);
		$users[] = array('username'=> "Capturista$i", 'district'=> $i, 'role' => 3);
	}
	for ($i=0; $i < 19; $i++) { 
		$passwords[] = $tb->generate_password(8,7);
	}
	
	$x = 0;
	foreach ($users as $user) {
		$u = new User();
		$u->username = $user['username'];
		$u->password = md5($passwords[$x]);
		$u->name = $user['username'];
		$u->district = $user['district'];
		$u->role = $user['role'];
		$u->save();
		unset($u);
		$x++;
	}
	ladybug_dump($passwords);
	ladybug_dump($users);*/
	/*$user = new User();
	$user->username = "capturista";
	$user->password = md5("capturista");
	$user->name = "Capturista";
	$user->district = 1;
	$user->role = 3;
	$user->save();*/
	
	/*for ($i=1; $i <= 8; $i++) {
		#INSERT INTO `assignations` (`id`,`district_id`,`activity_id`,`task_id`) VALUES('','','',''); 
		for ($j=1; $j <= 8; $j++) {
			switch ($j) {
				case '1': $begin = 1; $end = 5; break;
				case '2': $begin = 6; $end = 10; break;
				case '3': $begin = 11; $end = 15; break;
				case '4': $begin = 16; $end = 20; break;
				case '5': $begin = 21; $end = 25; break;
				case '6': $begin = 26; $end = 30; break;
				case '7': $begin = 31; $end = 35; break;
				case '8': $begin = 36; $end = 40; break;
			}
			for ($k=$begin; $k <= $end; $k++) { 
				$assignation = new Assignation();
				$assignation->district_id = $i;
				$assignation->activity_id = $j;
				$assignation->task_id = $k;
				$assignation->save();
				unset($assignation); 
				echo "Distric $i Activity $j Task $k<br/>";
			}
		}
	}*/
});

$app->run();

function update_assignation_status($id){
	$assignation = Assignation::find($id);
	$num_subs = count($assignation->subtasks);
	if($num_subs == 0){
		$status = 0;
	} else {
		$grade = 0;	
		foreach ($assignation->subtasks as $sub) {
			if($sub->start > time() || $sub->deleted == 1){
				$num_subs--;
			} else {
				$grade += $sub->progress;	
			}
		}
		if($num_subs <= 0){
			$status = 0;
		} else {
			$status = $grade / $num_subs;	
		}
	}
	
	$assignation->status = floor($status);
	$assignation->save();
}
