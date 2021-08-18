<?php

namespace Drupal\stud_solution\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\File\FileSystemInterface;


/// work with nodes and cache
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Cache\Cache;

use Drupal\file\Element;
use Drupal\file\Entity\File;

use Drupal\Core\Routing;



/**
 * Implements InputDemo form controller.
 *
 * This example demonstrates the different input elements that are used to
 * collect data in a form.
 */
class InputDemo extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $default_task=NULL) {
    $uid=\Drupal::currentUser()->id();


    $form['#attributes']['enctype'] = 'multipart/form-data';
    $form['#attributes']['autocomplete'] = 'off';	//firefox bug fix for select default
//    print_r($form);
//    die();

    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('This example shows the use of all input-types.'),
    ];

/*
    if($data['uid']=='-1')
     $data['uid']=\Drupal::currentUser()->id();
*/
    $result=\Drupal::entityQuery("node")
	->condition('type','task','=')
//	->condition('field_active',TRUE,'=')
	->execute();
//print_r($result);
//die();
    $storage_handler=\Drupal::entityTypeManager()->getStorage("node");
    $nodes=$storage_handler->loadMultiple($result);

    $results=array();
    foreach($nodes as $node)
    {
     $results[$node->get('field_st_tid')->getValue()[0]['value']]=
		    $node->getTitle();
    }


    // Select.
//    $default_task=$route_match->getRawParameters('default_task');
    $default_v=$default_task;
    $form['tid'] = [
      '#type' => 'select',
      '#title' => $this->t('Select task'),
      '#options' => $results,
      '#default_value' => $default_v,
      '#empty_option' => $this->t('-select-'),
      '#description' => $this->t('Select, #type = select'),
    ];

    // Manage file.
    $form['solution_fid'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://u'.$uid.'/solution_fid',
//      '#upload_location' => 'public://my',
//      '#type' => 'file',
      '#title' => 'Solution program',
      '#description' => $this->t('Manage file, #type = managed_file'),
      '#upload_validators'=> [
	'file_validate_extensions'=>['py']
	]
    ];

    // Manage file.
    $form['trained_params_fid'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://u'.$uid.'/trained_params_fid',
//      '#upload_location' => 'public://my',
//      '#type' => 'file',
      '#title' => 'Trained params(zip)',
      '#description' => $this->t('Manage file, #type = managed_file'),
      '#upload_validators'=> [
	'file_validate_extensions'=>['zip']
	]
    ];

    // Manage file.
    $form['training_program_fid'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://u'.$uid.'/training_program_fid',
//      '#upload_location' => 'public://my',
//      '#type' => 'file',
      '#title' => 'Training program',
      '#description' => $this->t('Manage file, #type = managed_file'),
      '#upload_validators'=> [
	'file_validate_extensions'=>['py']
	]
    ];


    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#description' => $this->t('Submit, #type = submit'),
    ];

    // Add a reset button that handles the submission of the form.
    $form['actions']['reset'] = [
      '#type' => 'button',
      '#button_type' => 'reset',
      '#value' => $this->t('Reset'),
      '#description' => $this->t('Submit, #type = button, #button_type = reset, #attributes = this.form.reset();return false'),
      '#attributes' => [
        'onclick' => 'this.form.reset(); return false;',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stud_solution_input_demo_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Find out what was submitted.
    $values = $form_state->getValues();

    $validators=array();
    if($values['solution_fid'] && $values['solution_fid'][0]>0)
    {
	$file=File::load($values['solution_fid'][0]);
	$file->setPermanent();
	$file->save();
    }
    
    if($values['trained_params_fid'] && $values['trained_params_fid'][0]>0)
    {
	$file=File::load($values['trained_params_fid'][0]);
	$file->setPermanent();
	$file->save();
    }

    if( $values['training_program_fid'] && $values['training_program_fid'][0]>0)
    {
	$file=File::load($values['training_program_fid'][0]);
	$file->setPermanent();
	$file->save();
    }

    foreach ($values as $key => $value) {
      $label = isset($form[$key]['#title']) ? $form[$key]['#title'] : $key;

    // Many arrays return 0 for unselected values so lets filter that out.
    if (is_array($value)) {
        $value = array_filter($value);
      }

      // Only display for controls that have titles and values.
      if ($value && $label) {
        $display_value = is_array($value) ? preg_replace('/[\n\r\s]+/', ' ', print_r($value, 1)) : $value;
//        $message = $this->t('Value for %title: %value', ['%title' => $label, '%value' => $display_value]);
//        $this->messenger()->addMessage($message);
      }
    }


    $uid=\Drupal::currentUser()->id();

    $node=Node::create(['type'=>'studentsolution']);
    $node->setTitle('solution for uid:'.$uid.' tid:'.($values['tid']+0));
    $node->set('field_processed_time','0');
    $node->set('field_task_id',$values['tid']+0);
    $node->set('field_result',0);
    $node->set('field_ss_solution_fid',$values['solution_fid']);
    $node->set('field_ss_trained_params_fid',$values['trained_params_fid']);
    $node->set('field_ss_training_program_fid',$values['training_program_fid']);
    $node->set('field_ss_uid',$uid);

//    $node->status=0;
    $node->status=1;
    $node->enforceIsNew();
    $node->save();
    $message = $this->t('Solution uploaded');
    $this->messenger()->addMessage($message);

    Cache::invalidateTags($node->getCacheTags());


  }

}



