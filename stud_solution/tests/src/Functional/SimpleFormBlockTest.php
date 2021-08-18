<?php

namespace Drupal\Tests\stud_solution\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests \Drupal\stud_solution\Plugin\Block\SimpleFormBlock.
 *
 * @group stud_solution
 * @group examples
 */
class SimpleFormBlockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'stud_solution'];

  /**
   * Test of paths through the example wizard form.
   */
  public function testSimpleFormBlock() {
    $assert = $this->assertSession();

    // Create user.
    $web_user = $this->drupalCreateUser(['administer blocks']);
    // Login the admin user.
    $this->drupalLogin($web_user);

    $theme_name = $this->config('system.theme')->get('default');

    // Place the block.
    $label = 'SimpleFormBlock-' . $this->randomString();
    $settings = [
      'label' => $label,
      'id' => 'stud_solution_simple_form_block',
      'theme' => $theme_name,
    ];
    $this->drupalPlaceBlock('stud_solution_simple_form_block', $settings);

    // Verify the block is present.
    $this->drupalGet('');
    $assert->pageTextContains($label);
    $assert->fieldExists('title');

    // And that the form works.
    $edit = [];
    $edit['title'] = 'SimpleFormBlock title example';
    $this->drupalPostForm(NULL, $edit, t('Submit'));
    $assert->pageTextContains('You specified a title of SimpleFormBlock title example');
  }

}
