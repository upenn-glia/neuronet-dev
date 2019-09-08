<?php

namespace Drupal\multiple_selects\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Render\Element;

/**
 * Plugin implementation of the 'options_select' widget.
 *
 * @FieldWidget(
 *   id = "multiple_options_select",
 *   label = @Translation("Multiple select list(s)"),
 *   field_types = {
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string"
 *   },
 * )
 */
class OptionsMultipleSelectWidget extends OptionsSelectWidget implements WidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $element = parent::form($items, $form, $form_state, $get_delta);
    $element['widget']['#element_validate'][] = array(get_class($this), 'validateMultipleElements');
    $element['widget']['#column'] = $this->column;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#required'] = FALSE;
    $option_element = parent::formElement($items, $delta, $element, $form, $form_state);
    $option_element['#multiple'] = FALSE;

    $element[$this->column] = $option_element;
    $element[$this->column]['#default_value'] = empty($items[$delta]->{$this->column}) ? '_none' : $items[$delta]->{$this->column};
    $element[$this->column]['#multiple'] = FALSE;
    unset($element['#type']);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateMultipleElements(array $element, FormStateInterface $form_state) {
    if ($element['#required'] == TRUE) {
      foreach (Element::children($element) as $key) {
        if (is_integer($key) && $element[$key][$element['#column']]['#value'] != '_none') {
          return;
        }
      }
      $form_state->setError($element[0], t('@name field is required.', array('@name' => $element['#title'])));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElement(array $element, FormStateInterface $form_state) {
    if (isset($element['#value']) && $element['#value'] === '_none') {
      $form_state->setValueForElement($element, NULL);
    }
  }

}
