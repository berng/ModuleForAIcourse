<?php

namespace Drupal\stud_autoproc\Plugin\QueueWorker;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "stud_autoproc_queue_2",
 *   title = @Translation("Second worker in stud_autoproc"),
 *   cron = {"time" = 20}
 * )
 *
 * QueueWorkers are new in Drupal 8. They define a queue, which in this case
 * is identified as stud_autoproc_queue_2 and contain a process that operates on
 * all the data given to the queue.
 *
 * @see queue_example.module
 */
class ReportWorkerTwo extends ReportWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->reportWork(2, $data);
  }

}
