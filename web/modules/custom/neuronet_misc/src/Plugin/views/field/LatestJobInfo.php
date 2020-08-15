<?php

namespace Drupal\neuronet_misc\Plugin\views\field;

use Drupal\node\Entity\Node;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for information about a profile's latest job.
 * 
 * 
 * @ingroup views_field_handlers
 * 
 * @ViewsField("latest_job")
 */
class LatestJobInfo extends FieldPluginBase {

    // /**
    //  * {@inheritdoc}
    //  */
    // protected function defineOptions() {
    //     $options = parent::defineOptions();

    //     $options['target_field'] = ['default' => ''];

    //     return $options;
    // }

    /**
     * {@inheritdoc}
     */
    public function query() {
        parent::query();
    }

    /**
     * {@inheritdoc}
     */
    public function render(ResultRow $values) {
        $node = $this->getEntity($values);
        if (!$node) {
            return [];
        }

        /** @var Node[] $jobs */
        $jobs = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
            'field_profile' => $node->id(),
            'type' => 'job',
        ]);

        /** @var Node $latestJob */
        $latestJob = NULL;
        $latestStartDate = 0;

        foreach ($jobs as $job) {
            if ($job->hasField('field_start_date')) {
                $startDate = $job->get('field_start_date')->first()->getValue();

                if ($latestJob === NULL || $startDate > $latestStartDate) {
                    $latestJob = $job;
                    $latestStartDate = $startDate;
                }
            }
        }

        if ($latestJob && $latestJob->hasField('field_job_term')) {
            return $latestJob->get('field_job_term');
        } else {
            return [];
        }
    }
}