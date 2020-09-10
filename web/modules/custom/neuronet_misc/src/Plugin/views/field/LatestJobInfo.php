<?php

namespace Drupal\neuronet_misc\Plugin\views\field;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler for information about a profile's latest job.
 * 
 * 
 * @ingroup views_field_handlers
 * 
 * @ViewsField("latest_job")
 */
class LatestJobInfo extends FieldPluginBase {

    /**
     * The entity typemanager.
     *
     * @var EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * The entity field manager.
     * 
     * @var EntityFieldManagerInterface
     */
    protected $entityFieldManager;

    /**
     * Holds the names of job fields and whether they are entity references.
     * 
     * @var array
     */
    protected $jobFields;

    /**
     * Constructs a LatestJobInfo field plugin.
     * 
     * @param array $configuration
     *  A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *  The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *  The plugin implementation definition.
     * @param EntityTypeManagerInterface $entity_type_manager
     *  The entity type manager.
     * @param EntityFieldManagerInterface $entity_field_manager
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition,
        EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);

        $this->entityTypeManager = $entity_type_manager;
        $this->entityFieldManager = $entity_field_manager;

        // Populate jobFields
        $this->refreshJobFields();
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('entity_type.manager'),
            $container->get('entity_field.manager')
        );
    }

    /**
     * Update $jobFields to make sure we have all the latest info
     */
    private function refreshJobFields() {
        $this->jobFields = [];

        $allFieldDefs = $this->entityFieldManager->getFieldDefinitions('node', 'job');
        $validNonfields = ['title', 'nid'];

        foreach ($allFieldDefs as $key => $value) {
            if (strncmp($key, 'field_', 6) == 0 || in_array($key, $validNonfields)) {
                $this->jobFields[$key] = [
                    'name' => $value->getLabel(),
                    'is_ref' => $value->getType() === 'entity_reference'
                ];
            }
        }

        asort($this->jobFields);
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions() {
        $options = parent::defineOptions();

        $options['target_field'] = ['default' => 'title'];

        # if target field is an entity reference, this controls whether to use a link to entity
        $options['link'] = ['default' => FALSE];

        return $options;
    }

    /**
     * Create the options form where users can choose which job field to display
     * and whether to link to content for entity reference fields.
     */
    public function buildOptionsForm(&$form, FormStateInterface $form_state) {
        parent::buildOptionsForm($form, $form_state);

        // Get possible fields
        $this->refreshJobFields();

        // Make drop-down for field
        $form['target_field'] = [
            '#type' => 'select',
            '#title' => $this->t('Job field to show'),
            '#options' => array_combine(array_keys($this->jobFields), array_column($this->jobFields, 'name')),
            '#default_value' => $this->options['target_field'],
            '#required' => TRUE,
        ];

        $form['link'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Link to referenced entity'),
            '#default_value' => $this->options['link'],
            '#description' => $this->t('If an entity reference is selected, this controls whether to link to it.'),
        ];
    }

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
        $jobs = $this->entityTypeManager->getStorage('node')->loadByProperties([
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

        $fieldname = $this->options['target_field'];
        if ($latestJob && $latestJob->hasField($fieldname)) {

            /** @var  FieldItemInterface $fieldToShow */
            $fieldToShow = $latestJob->get($fieldname)->first();

            $display_options = [];
            if ($this->jobFields[$fieldname]['is_ref']) {
                $display_options['settings'] = ['link' => $this->options['link']];
            }

            return $fieldToShow->view($display_options);
        } else {
            return [];
        }
    }
}