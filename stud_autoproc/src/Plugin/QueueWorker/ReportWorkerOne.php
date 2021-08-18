<?php

namespace Drupal\stud_autoproc\Plugin\QueueWorker;

/**
 * A report worker.
 *
 * @QueueWorker(
 *   id = "stud_autoproc_queue_1",
 *   title = @Translation("First worker in stud_autoproc"),
 *   cron = {"time" = 1}
 * )
 *
 * QueueWorkers are new in Drupal 8. They define a queue, which in this case
 * is identified as stud_autoproc_queue_1 and contain a process that operates on
 * all the data given to the queue.
 *
 * @see queue_example.module
 */
class ReportWorkerOne extends ReportWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $this->reportWork(1, $data);
  }

}
