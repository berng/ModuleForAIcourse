<?php

namespace Drupal\Tests\stud_solution\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\Url;

/**
 * @group stud_solution
 *
 * @ingroup stud_solution
 */
class StateDemoTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Our module dependencies.
   *
   * @var string[]
   */
  public static $modules = ['stud_solution'];

  /**
   * Functional tests for the StateDemo example form.
   */
  public function testStateForm() {
    // Visit form route.
    $route = Url::fromRoute('stud_solution.state_demo');
    $this->drupalGet($route);

    // Get Mink stuff.
    $page = $this->getSession()->getPage();

    // Verify we can find the diet restrictions textfield, and that by default
    // it is not visible.
    $this->assertNotEmpty($checkbox = $page->find('css', 'input[name="diet"]'));
    $this->assertFalse($checkbox->isVisible(), 'Diet restrictions field is not visible.');

    // Check the needs special accommodation checkbox.
    $page->checkField('needs_accommodation');

    // Verify the textfield is visible now.
    $this->assertTrue($checkbox->isVisible(), 'Diet restrictions field is visible.');
  }

}
