<?php

namespace Drupal\stud_solution\Controller;

use Drupal\examples\Utility\DescriptionTemplateTrait;

/**
 * Simple page controller for drupal.
 */
class Page {

  use DescriptionTemplateTrait;

  /**
   * {@inheritdoc}
   */
  public function getModuleName() {
    return 'stud_solution';
  }

}
