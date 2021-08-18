<?php

namespace Drupal\Tests\stud_sol_list\Functional;

use Drupal\Tests\examples\Functional\ExamplesBrowserTestBase;

/**
 * Tests paging.
 *
 * @group stud_sol_list
 * @group examples
 */
class StudSolListTest extends ExamplesBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['stud_sol_list', 'node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Log in a user to prevent caching from affecting the results.
    $normalUser = $this->drupalCreateUser();
    $this->drupalLogin($normalUser);
  }

  /**
   * Confirms nodes paging works correctly on page "stud_sol_list".
   */
  public function testPagerExamplePage() {
    $assert = $this->assertSession();

    $nodes = [];
    $nodes[] = $this->drupalCreateNode();

    $this->drupalGet('examples/stud-sol-list');
    $assert->linkNotExists('Next');
    $assert->linkNotExists('Previous');

    // Create 5 new nodes.
    for ($i = 1; $i <= 5; $i++) {
      $nodes[] = $this->drupalCreateNode([
        'title' => "Node number $i",
      ]);
    }

    // The pager pages are cached, so flush to see the 5 more nodes.
    drupal_flush_all_caches();

    // Check 'Next' link on first page.
    $this->drupalGet('examples/stud-sol-list');
    $assert->statusCodeEquals(200);
    $assert->linkByHrefExists('?page=1');
    $assert->pageTextContains($nodes[5]->getTitle());

    // Check the last page.
    $this->drupalGet('examples/stud-sol-list', ['query' => ['page' => 2]]);
    $assert->statusCodeEquals(200);
    $assert->linkNotExists('Next');
    $assert->linkByHrefExists('?page=1');
    $assert->pageTextContains($nodes[1]->getTitle());
  }

}
