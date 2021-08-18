<?php

namespace Drupal\stud_sol_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Element;
use Drupal\file\Entity\File;
use Drupal\Core\Render\Markup;

use Drupal\Core\Cache\Cache;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

//use Drupal\Core\File;
//use Drupal\Core\File\FileSystem;


/**
 * Controller for stud_sol_list.page route.
 *
 * This is an example describing how a module can implement a pager in order to
 * reduce the number of output rows to the screen and allow a user to scroll
 * through multiple screens of output.
 */
class StudSolListPage extends ControllerBase {

  /**
   * Entity storage for node entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * PagerExamplePage constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   Entity storage for node entities.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(EntityStorageInterface $node_storage, AccountInterface $current_user) {
    $this->nodeStorage = $node_storage;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $controller = new static(
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('current_user')
    );
    $controller->setStringTranslation($container->get('string_translation'));
    return $controller;
  }


  private function processSolution($node)
    {
	    $tid=$node->get('field_task_id')->getValue()[0]['value'];
	    $result=\Drupal::entityQuery("node")
		    ->condition('type','task','=')
		    ->condition('field_st_tid',$tid,'=')
		    ->execute();
	    $storage_handler=\Drupal::entityTypeManager()->getStorage("node");
	    $tasks=$storage_handler->loadMultiple($result);

	/// read files 
	    foreach($tasks as $task)
	     { break; }

	    $uid=$node->get('field_ss_uid')->getValue()[0]['value'];
	    $res1=shell_exec('rm -rf tmp_user_data/u-'.$uid);
	    $res1=shell_exec('mkdir tmp_user_data/u-'.$uid);

//	    print('task:');
//	    print_r($task);
	    if($task)
	    {
		$file_id=$task->get('field_file_test')->getValue()[0]['target_id'];
		$file_test=File::load($file_id);
		$fname6=$file_test->getFileUri();
		$fname61=\Drupal::service('file_system')->copy($fname6,
					    "tmp_user_data/u-$uid/src_data.dat",FILE_EXISTS_RENAME);

		$file_id2=$task->get('field_call_function')->getValue()[0]['target_id'];
		$file_test2=File::load($file_id2);
		$fname7=$file_test2->getFileUri();
		$fname71=\Drupal::service('file_system')->copy($fname7,
					    "tmp_user_data/u-$uid/process_all.py",FILE_EXISTS_RENAME);
	    }

//get files	
	$file_sol=File::load($node->get('field_ss_solution_fid')->getValue()[0]['value']);
	$file_tp=File::load($node->get('field_ss_trained_params_fid')->getValue()[0]['value']);
	$file_tprog=File::load($node->get('field_ss_training_program_fid')->getValue()[0]['value']);
///
	$fname2=$file_sol->getFileUri();
	$fname2_tp=$file_tp->getFileUri();

	$fname3=\Drupal::service('file_system')->copy($fname2,"tmp_user_data/u-$uid/myclass.py",FILE_EXISTS_RENAME);
	$fname4=\Drupal::service('file_system')->copy($fname2_tp,"tmp_user_data/u-$uid/trained_params.zip",FILE_EXISTS_RENAME);
//	$fname3=file_copy($file_sol,'temporary://user_tmp/sol.py',FILE_EXISTS_RENAME);
//	file_prepare_directory('public://user_tmp/sol_tmp.py',FILE_CREATE_DIRECTORY);

/*
	$dir='public://user_tmp/';
	\Drupal::service('file_system')->prepareDirectory($dir,
			\Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY
//			|
//			\Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS
			);

//	$file_tmp=file_copy($file_sol,'public://user_tmp/sol_tmp.py',FILE_EXISTS_REPLACE);

	$file_tmp->setTemporary();
	$file_tmp->save();
*/	
	
//create processed solutions module
//	print('res01:'.$res1."\n<br>");
//	$res1=exec('date');
//	$res2=exec('whoami && pwd && ls -la /home/oleg');
//	$res1=shell_exec('whoami && pwd && mkdir tmp_user_data/aa1 && ls tmp_user_data');
/*
	$f=fopen('/var/www/html/drupal/tmp_user_data/my.dat','w');
	fwrite($f,"#!/usr/bin/python3\nprint('hello python:',(1+2+7))");
	fclose($f);
*/
	$res=shell_exec("cd tmp_user_data/u-$uid/ && /usr/bin/unzip trained_params.zip 1>2 2>/dev/null && /usr/bin/python3 ./process_all.py");
//	$res2=shell_exec("/usr/bin/python3 tmp_user_data/u-$uid/user_solution.py < tmp_user_data/u-$uid/src_data.dat");
//	$res2=system("/usr/bin/python3 tmp_user_data/u-$uid/user_solution.py < tmp_user_data/u-$uid/src_data.dat");
#	$res2=system("cd tmp_user_data/u-$uid/ && cat ./src_data.dat | /usr/bin/python3 ./user_solution.py");
//	$res2=system("cd tmp_user_data/u-$uid/ && /usr/bin/python3 ./user_solution.py");
//	$res3=shell_exec("rm -rf tmp_user_data/u-$uid");

//	print('res21:'.$res1."\n<br>");
//	print('res23:'.$res3."\n<br>");
//	print('res22:'.$res2."\n<br>====\n<br>");
//	die();

	$ps_node=Node::create([
				'type'=>'processedsolutions',
				'created'=>1,
				'title'=>$node->getTitle(),
				]);
	$ps_node->set('field_uid',$node->get('field_ss_uid')->getValue()[0]['value']);
	$ps_node->set('field_tid',$node->get('field_task_id')->getValue()[0]['value']);
	$ps_node->set('field_solution_id',$node->id());
	$ps_node->set('field_ps_result',$res*100.0);
	$ps_node->set('field_process_date',time());
	$ps_node->set('field_ps_debug','res1:'.$res1);
	$ps_node->set('field_ps_errors','res2:'.$res2);
//	$ps_node->status=0;
	$ps_node->enforceIsNew(TRUE);
	$ps_node->save();
	Cache::invalidateTags($ps_node->getCacheTags());

// update student solutions node
	$node->set('field_result',$res*100.0);
	$node->set('field_processed_time',time());
	$node->save();
	Cache::invalidateTags($node->getCacheTags());
    }



  private function removeNodeContent($node) 
    {
	/// delete files 
	$file=File::load($node->get('field_ss_solution_fid')->getValue()[0]['value']);
	if($file) $file->delete();
	$file=File::load($node->get('field_ss_trained_params_fid')->getValue()[0]['value']);
	if($file) $file->delete();
	$file=File::load($node->get('field_ss_training_program_fid')->getValue()[0]['value']);
	if($file) $file->delete();
    }


  /**
   * Content callback for the stud_sol_list.page route.
   */
  public function getContent($action='ls',$ssid=-1) {
    // First we'll tell the user what's going on. This content can be found
    // in the twig template file: templates/description.html.twig. It will be
    // inserted by the theming function stud_sol_list_description().

    switch($action)
    {
	case 'rm':	///remove solution and related files
	    $result=\Drupal::entityQuery("node")
		    ->condition('type','studentsolution','=')
		    ->condition('nid',$ssid,'=')
		    ->execute();
	    $storage_handler=\Drupal::entityTypeManager()->getStorage("node");
	    $nodes=$storage_handler->loadMultiple($result);

	/// delete nodes
	    foreach($nodes as $node)
	     { $this->removeNodeContent($node); }
	    $storage_handler->delete($nodes);
	    break;

	case 'pr':	///process solution and related files

	    $result=\Drupal::entityQuery("node")
		    ->condition('type','studentsolution','=')
		    ->condition('nid',$ssid,'=')
		    ->execute();
	    $storage_handler=\Drupal::entityTypeManager()->getStorage("node");
	    $nodes=$storage_handler->loadMultiple($result);

	/// read files 
	    foreach($nodes as $node)
	     { $this->processSolution($node); }
	    break;

	case 'ls':
	default: 

    }
    $build = [
      'description' => [
        '#theme' => 'stud_sol_list_description',
        '#description' => $this->t('description'),
        '#attributes' => [],
      ],
    ];

    // Ensure that this page's cache is invalidated when nodes have been
    // published, unpublished, added or deleted; and when user permissions
    // change.
    $build['#cache']['tags'][] = 'node_list';
    $build['#cache']['contexts'][] = 'user.permissions';

    // Now we want to get our tabular data. We select nodes from node storage
    // limited by 2 per page and sort by nid DESC because we want to show newest
    // node first. Additionally, we check that the user has permission to
    // view the node.

    $query = $this->nodeStorage->getQuery()
      ->condition('type', 'task')
//      ->sort('title', 'ASC')
      ->sort('field_st_tid', 'ASC')
//      ->addTag('node_access')
      ->pager(5);
//    $query->condition('status', 1);

    if (!$this->currentUser->hasPermission('bypass node access')) {
//      $query->condition('status', 0);
    }
    $entity_ids = $query->execute();
    $entity_ids2= $entity_ids;

    $nodes = $this->nodeStorage->loadMultiple($entity_ids);
    $rows_tasks = [];
    foreach ($nodes as $node) {
      $rows_tasks[] = [
//        'nid' => $node->access('view') ? $node->id() : $this->t('XXXXXX'),
        'title' => $node->access('view') ? Markup::create('<a href="/drupal/node/'.$node->id().'" target="__blank">'.$node->getTitle() .'</a>') : $this->t('Redacted'),
        'tid' => $node->get('field_st_tid')->getValue()[0]['value'],
      ];
    }



    $uid=\Drupal::currentUser()->id();
    $query = $this->nodeStorage->getQuery()
      ->condition('type', 'StudentSolution')
      ->condition('field_ss_uid', $uid ,'=')
      ->sort('nid', 'DESC')
//      ->addTag('node_access')
//      ->pager(5)
	;
//print("uid:",$uid);
//die();

    // The node_access tag does not trigger a check on whether a user has the
    // ability to view unpublished content. The 'bypass node access' permission
    // is really more than we need. But, there is no separate permission for
    // viewing unpublished content. There is a permission to 'view own
    // unpublished content', but we don't have a good way of using that in this
    // query. So, unfortunately this query will incorrectly eliminate even those
    // unpublished nodes that the user may, in fact, be allowed to view.
    if (!$this->currentUser->hasPermission('bypass node access')) {
//      $query->condition('status', 0);
    }
    $entity_ids = $query->execute();

    $nodes = $this->nodeStorage->loadMultiple($entity_ids);

    // We are going to output the results in a table so we set up the rows.
    $rows = [];
    foreach ($nodes as $node) {
      // There are certain things (besides unpublished nodes) that the
      // node_access tag won't prevent from being seen. The only way to get at
      // those is by explicitly checking for (view) access on a node-by-node
      // basis. In order to prevent the pager from looking strange, we will
      // "mask" these nodes that should not be accessible. If we don't do this
      // masking, it's possible that we'd have lots of pages that don't show any
      // content.
    $file=File::load($node->get('field_ss_solution_fid')->getValue()[0]['value']);
    if($file)
      $uri_sol=file_create_url($file->getFileUri());
    else
      $uri_sol='';

    $file=File::load($node->get('field_ss_trained_params_fid')->getValue()[0]['value']);
    if($file)
      $uri_tprms=file_create_url($file->getFileUri());
    else
      $uri_tprms='';

    $file=File::load($node->get('field_ss_training_program_fid')->getValue()[0]['value']);
    if($file)
      $uri_tp=file_create_url($file->getFileUri());
    else
      $uri_tp='';

    $uri_rm='/rm/'.$node->id();
    $uri_pr='/pr/'.$node->id();

      $rows[] = [
//        'nid' => $node->access('view') ? $node->id() : $this->t('XXXXXX'),
//        'title' => $node->access('view') ? Markup::create('<a href="/drupal/node/'.$node->id().'" target="__blank">'.$node->getTitle() .'</a>'): $this->t('Redacted'),
        'task' => $node->access('view') ? $node->get('field_task_id')->getValue()[0]['value'] : $this->t('XXXXXX'),
        'processed' => $node->access('view') ? date(DATE_RFC822,$node->get('field_processed_time')->getValue()[0]['value']) : $this->t('XXXXXX'),
        'result' => $node->access('view') ? $node->get('field_result')->getValue()[0]['value'] : $this->t('XXXXXX'),
        'solution' => ($uri_sol)?Markup::create('<a href="'.$uri_sol.'" target="__blank">Скачать решение</a>'):'',
        'trained_params_fid' => ($uri_tprms)?Markup::create('<a href="'.$uri_tprms.'" target="__blank">Скачать параметры обучения</a>'):'',
        'training_program_fid' => ($uri_tp)?Markup::create('<a href="'.$uri_tp.'" target="__blank">Скачать программу обучения</a>'):'',
        'actions' =>Markup::create(
//'<a href="/drupal/examples/stud-sol-list'.$uri_pr.'">Process</a><br>|'.
'<a href="/drupal/examples/stud-sol-list'.$uri_rm.'">Удалить решение</a><br>'
//		(($uri_sol && $uri_tprms && $uri_tp)?'<a href="/drupal/examples/stud-sol-list'.$uri_pr.'">Process</a><br>':'').
//		((!$uri_sol || !$uri_tprms || !$uri_tp)?'<a href="/drupal/examples/stud-sol-list'.$uri_rm.'">Remove</a><br>':'')
			),
      ];
    }

    foreach ($rows_tasks as $key=>$value)
      {
	$found=false;
	foreach ($rows as $key2=>$value2)
	 {
	    if($value2['task']==$value['tid'])
		{
		 foreach ($value2 as $key3=>$value3)
		  $rows_tasks[$key]['s_'.$key3]=$value3;
	    	 $found=true;
		 break;
		}
	 }
	if(!$found)
	{
//	  $rows_tasks[$key]['s_nid'] = '';
//          $rows_tasks[$key]['s_title'] = '';
          $rows_tasks[$key]['s_task'] = '';
          $rows_tasks[$key]['s_processed'] = 'Не решена';
          $rows_tasks[$key]['s_result'] = '';
          $rows_tasks[$key]['s_solution'] = '';
          $rows_tasks[$key]['s_trained_params_fid'] = '';
          $rows_tasks[$key]['s_training_program_fid'] = '';
	  $rows_tasks[$key]['s_actions']=Markup::create(
		'<a href="/drupal/examples/stud-solution/input-demo/'.$value['tid'].'">Загрузить решение</a><br>'
		);
	}
      }
/*
    // Build a render array which will be themed as a table with a pager.
    $build['stud_sol_list'] = [
      '#type' => 'table',
      '#header' => [$this->t('NID'), 
		    $this->t('Title'), 
		    $this->t('Task')
		    ,$this->t('Processed')
		    , $this->t('Result')
		    , $this->t('Solution')
		    , $this->t('Trained params')
		    , $this->t('Training program')
		    , $this->t('Actions')
		    ],
      '#rows' => $rows,
      '#empty' => $this->t('There are no nodes to display. Please <a href=":url">create a node</a>.', [
        ':url' => Url::fromRoute('node.add', ['node_type' => 'page'])->toString(),
      ]),
    ];
*/
    // Add our pager element so the user can choose which pagination to see.
    // This will add a '?page=1' fragment to the links to subsequent pages.
    $build['pager'] = [
      '#type' => 'pager',
      '#weight' => 10,
    ];

    $build['stud_task_list'] = [
      '#type' => 'table',
      '#header' => [
//		    $this->t('NID'), 
		    $this->t('Задача'),
		    $this->t('№'),
//		    $this->t('NID'), 
//		    $this->t('Title'), 
		    $this->t('№'),
		    $this->t('Дата и время обработки')
		    , $this->t('Результат (баллы)')
		    , $this->t('Решение')
		    , $this->t('Обученные параметры')
		    , $this->t('Программа для обучения')
		    , $this->t('Действия')

		    ],
      '#rows' => $rows_tasks,
      '#empty' => $this->t(''),
    ];


    return $build;
  }



  /**
   * Content callback for the stud_sol_list.admin_page route.
   */
  public function getAdminContent($action='ls',$ssid=-1) {
    // First we'll tell the user what's going on. This content can be found
    // in the twig template file: templates/description.html.twig. It will be
    // inserted by the theming function stud_sol_list_description().

    switch($action)
    {
	case 'rm':	///remove solution and related files
	    $result=\Drupal::entityQuery("node")
		    ->condition('type','studentsolution','=')
		    ->condition('nid',$ssid,'=')
		    ->execute();
	    $storage_handler=\Drupal::entityTypeManager()->getStorage("node");
	    $nodes=$storage_handler->loadMultiple($result);

	/// delete nodes
	    foreach($nodes as $node)
	     { $this->removeNodeContent($node); }
	    $storage_handler->delete($nodes);
	    break;

	case 'pr':	///process solution and related files

	    $result=\Drupal::entityQuery("node")
		    ->condition('type','studentsolution','=')
		    ->condition('nid',$ssid,'=')
		    ->execute();
	    $storage_handler=\Drupal::entityTypeManager()->getStorage("node");
	    $nodes=$storage_handler->loadMultiple($result);

	/// read files 
	    foreach($nodes as $node)
	     { $this->processSolution($node); }
	    break;

	case 'ls':
	default: 

    }
    $build = [
      'description' => [
        '#theme' => 'stud_sol_list_description_admin',
        '#description' => $this->t('description'),
        '#attributes' => [],
      ],
    ];

    // Ensure that this page's cache is invalidated when nodes have been
    // published, unpublished, added or deleted; and when user permissions
    // change.
    $build['#cache']['tags'][] = 'node_list';
    $build['#cache']['contexts'][] = 'user.permissions';

    // Now we want to get our tabular data. We select nodes from node storage
    // limited by 2 per page and sort by nid DESC because we want to show newest
    // node first. Additionally, we check that the user has permission to
    // view the node.

    $query = $this->nodeStorage->getQuery()
      ->condition('type', 'task')
      ->sort('title', 'ASC')
//      ->addTag('node_access')
//      ->pager(1)
;
//    $query->condition('status', 1);

    if (!$this->currentUser->hasPermission('bypass node access')) {
//      $query->condition('status', 0);
    }
    $entity_ids = $query->execute();
    $entity_ids2= $entity_ids;

    $nodes = $this->nodeStorage->loadMultiple($entity_ids);
    $rows_tasks = [];
    foreach ($nodes as $node) {
      $rows_tasks[] = [
        'nid' => $node->access('view') ? $node->id() : $this->t('XXXXXX'),
        'title' => $node->access('view') ? $node->getTitle() : $this->t('Redacted'),
        'tid' => $node->get('field_st_tid')->getValue()[0]['value'],
      ];
    }



    $uid=\Drupal::currentUser()->id();
    $query = $this->nodeStorage->getQuery()
      ->condition('type', 'StudentSolution')
      ->condition('field_ss_uid', $uid ,'=')
      ->sort('nid', 'DESC')
//      ->addTag('node_access')
      ->pager(5)
	;
//print("uid:",$uid);
//die();

    // The node_access tag does not trigger a check on whether a user has the
    // ability to view unpublished content. The 'bypass node access' permission
    // is really more than we need. But, there is no separate permission for
    // viewing unpublished content. There is a permission to 'view own
    // unpublished content', but we don't have a good way of using that in this
    // query. So, unfortunately this query will incorrectly eliminate even those
    // unpublished nodes that the user may, in fact, be allowed to view.
    if (!$this->currentUser->hasPermission('bypass node access')) {
//      $query->condition('status', 0);
    }
    $entity_ids = $query->execute();

    $nodes = $this->nodeStorage->loadMultiple($entity_ids);

    // We are going to output the results in a table so we set up the rows.
    $rows = [];
    foreach ($nodes as $node) {
      // There are certain things (besides unpublished nodes) that the
      // node_access tag won't prevent from being seen. The only way to get at
      // those is by explicitly checking for (view) access on a node-by-node
      // basis. In order to prevent the pager from looking strange, we will
      // "mask" these nodes that should not be accessible. If we don't do this
      // masking, it's possible that we'd have lots of pages that don't show any
      // content.
    $file=File::load($node->get('field_ss_solution_fid')->getValue()[0]['value']);
    if($file)
      $uri_sol=file_create_url($file->getFileUri());
    else
      $uri_sol='';

    $file=File::load($node->get('field_ss_trained_params_fid')->getValue()[0]['value']);
    if($file)
      $uri_tprms=file_create_url($file->getFileUri());
    else
      $uri_tprms='';

    $file=File::load($node->get('field_ss_training_program_fid')->getValue()[0]['value']);
    if($file)
      $uri_tp=file_create_url($file->getFileUri());
    else
      $uri_tp='';

    $uri_rm='/rm/'.$node->id();
    $uri_pr='/pr/'.$node->id();

      $rows[] = [
        'nid' => $node->access('view') ? $node->id() : $this->t('XXXXXX'),
        'title' => $node->access('view') ? $node->getTitle() : $this->t('Redacted'),
        'task' => $node->access('view') ? $node->get('field_task_id')->getValue()[0]['value'] : $this->t('XXXXXX'),
        'processed' => $node->access('view') ? $node->get('field_processed_time')->getValue()[0]['value'] : $this->t('XXXXXX'),
        'result' => $node->access('view') ? $node->get('field_result')->getValue()[0]['value'] : $this->t('XXXXXX'),
        'solution' => ($uri_sol)?Markup::create('<a href="'.$uri_sol.'" target="__blank">Download</a>'):'',
        'trained_params_fid' => ($uri_tprms)?Markup::create('<a href="'.$uri_tprms.'" target="__blank">Download</a>'):'',
        'training_program_fid' => ($uri_tp)?Markup::create('<a href="'.$uri_tp.'" target="__blank">Download</a>'):'',
        'actions' =>Markup::create(
'<a href="/drupal/examples/stud-sol-list'.$uri_pr.'">Process</a><br>|'.
'<a href="/drupal/examples/stud-sol-list'.$uri_rm.'">Remove</a><br>'
//		(($uri_sol && $uri_tprms && $uri_tp)?'<a href="/drupal/examples/stud-sol-list'.$uri_pr.'">Process</a><br>':'').
//		((!$uri_sol || !$uri_tprms || !$uri_tp)?'<a href="/drupal/examples/stud-sol-list'.$uri_rm.'">Remove</a><br>':'')
			),
      ];
    }

/*
    foreach ($rows_tasks as $key=>$value)
      {
	foreach ($rows as $key2=>$value2)
	 {
	    if($value2['task']==$value['tid'])
		{
		 foreach ($value2 as $key3=>$value3)
		  $rows_tasks[$key]['s_'.$key3]=$value3;
		 break;
		}
	 }
      }
*/

    // Build a render array which will be themed as a table with a pager.
    $build['stud_sol_list'] = [
      '#type' => 'table',
      '#header' => [$this->t('NID'), 
		    $this->t('Title'), 
		    $this->t('Task')
		    ,$this->t('Processed')
		    , $this->t('Result')
		    , $this->t('Solution')
		    , $this->t('Trained params')
		    , $this->t('Training program')
		    , $this->t('Actions')
		    ],
      '#rows' => $rows,
      '#empty' => $this->t(''),
    ];

    // Add our pager element so the user can choose which pagination to see.
    // This will add a '?page=1' fragment to the links to subsequent pages.
    $build['pager'] = [
      '#type' => 'pager',
      '#weight' => 10,
    ];


/*
    $build['stud_task_list'] = [
      '#type' => 'table',
      '#header' => [$this->t('NID'), 
		    $this->t('Title'),
		    $this->t('TID'),
		    $this->t('NID'), 
		    $this->t('Title'), 
		    $this->t('Task')
		    ,$this->t('Processed')
		    , $this->t('Result')
		    , $this->t('Solution')
		    , $this->t('Trained params')
		    , $this->t('Training program')
		    , $this->t('Actions')

		    ],
      '#rows' => $rows_tasks,
      '#empty' => $this->t(''),
    ];
*/

    return $build;
  }




}
