<?php

namespace Drupal\Tests\stud_autoproc\Functional;

use Drupal\Core\Url;
use Drupal\Tests\examples\Functional\ExamplesBrowserTestBase;

/**
 * Test the functionality for the Cron Example.
 *
 * @ingroup stud_autoproc
 *
 * @group stud_autoproc
 * @group examples
 */
class StudAutoprocTest extends ExamplesBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['stud_autoproc', 'node'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Create user. Search content permission granted for the search block to
    // be shown.
    $this->drupalLogin($this->drupalCreateUser(['administer site configuration', 'access content']));
  }

  /**
   * Create an example node, test block through admin and user interfaces.
   */
  public function testStudAutoprocBasic() {
    $assert = $this->assertSession();

    $cron_form = Url::fromRoute('stud_autoproc.description');

    // Pretend that cron has never been run (even though simpletest seems to
    // run it once...).
    $this->container->get('state')->set('stud_autoproc.next_execution', 0);
    $this->drupalGet($cron_form);

    // Initial run should cause stud_autoproc_cron() to fire.
    $post = [];
    $this->drupalPostForm($cron_form, $post, 'Run cron now');
    $assert->pageTextContains('stud_autoproc executed at');

    // Forcing should also cause stud_autoproc_cron() to fire.
    $post['cron_reset'] = TRUE;
    $this->drupalPostForm(NULL, $post, 'Run cron now');
    $assert->pageTextContains('stud_autoproc executed at');

    // But if followed immediately and not forced, it should not fire.
    $post['cron_reset'] = FALSE;
    $this->drupalPostForm(NULL, $post, 'Run cron now');
    $assert->statusCodeEquals(200);
    $assert->pageTextNotContains('stud_autoproc executed at');
    $assert->pageTextContains('There are currently 0 items in queue 1 and 0 items in queue 2');

    $post = [
      'num_items' => 5,
      'queue' => 'stud_autoproc_queue_1',
    ];
    $this->drupalPostForm(NULL, $post, 'Add jobs to queue');
    $assert->pageTextContains('There are currently 5 items in queue 1 and 0 items in queue 2');

    $post = [
      'num_items' => 100,
      'queue' => 'stud_autoproc_queue_2',
    ];
    $this->drupalPostForm(NULL, $post, 'Add jobs to queue');
    $assert->pageTextContains('There are currently 5 items in queue 1 and 100 items in queue 2');

    $this->drupalPostForm($cron_form, [], 'Run cron now');
    $assert->responseMatches('/Queue 1 worker processed item with sequence 5 /');
    $assert->responseMatches('/Queue 2 worker processed item with sequence 100 /');
  }

}
