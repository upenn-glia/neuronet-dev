<?php
 
/**
 * @file
 * Definition of Drupal\views\Plugin\views\field\NodeCountCompletedFields
 */
 
namespace Drupal\neuronet_misc\Plugin\views\field;
 
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
 
/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("node_count_completed_fields")
 */
class NodeCountCompletedFields extends FieldPluginBase {
 
  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }
 
  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }
 
  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }
 
  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $node = $values->_entity;
    $count = 0;
    foreach($node->getFieldDefinitions() as $field) {
      $name = $field->getName();
      if (!empty($name) && strpos($name, 'field_') !== false && $name != "field_alumni" && $name != 'field_degree') {
        $value = $node->get($name)->getValue();
        if (!empty($value)) {
          $count++;
        }
      }
    }
    return $count;
  }
}