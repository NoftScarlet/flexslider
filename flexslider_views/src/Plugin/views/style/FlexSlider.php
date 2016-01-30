<?php

/**
 * @file
 * Definition of Drupal\flexslider_views\Plugin\views\style\FlexSlider.
 *
 * @author Agnes Chisholm <amaria@66428.no-reply.drupal.org>
 */

namespace Drupal\flexslider_views\Plugin\views\style;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render each item in an ordered or unordered list.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "flexslider",
 *   title = @Translation("FlexSlider"),
 *   help = @Translation("Display the results in a FlexSlider widget."),
 *   theme = "flexslider_views_style",
 *   theme_file = "flexslider_views.theme.inc",
 *   display_types = {"normal"}
 * )
 */
class FlexSlider extends StylePluginBase {
  /**
   * {@inheritdoc}
   */
  protected $usesRowPlugin = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesFields = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $usesOptions = TRUE;

  /**
   * {@inheritdoc}
   */
  public function evenEmpty() {
    return FALSE;
  }
  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['optionset'] = array('default' => 'default');
    $options['captionfield'] = array('default' => '');
    $options['id'] = array('default' => '');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['flexslider'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('FlexSlider'),
    );

    $form['flexslider']['optionset'] = array(
      '#title' => t('Option set'),
      '#type' => 'select',
      '#options' => flexslider_optionset_list(),
      '#default_value' => $this->options['optionset'],
    );

    $captionfield_options = array('' => $this->t('None'));
    foreach ($this->displayHandler->getHandlers('field') as $field => $handler) {
      $captionfield_options[$field] = $handler->adminLabel();
    }

    $form['flexslider']['captionfield'] = array(
      '#type' => 'select',
      '#title' => $this->t('Caption Field'),
      '#description' => $this->t("Select a field to be used as the caption. This can also be set manually by adding the '.flex-caption' class to a field. Required to use thumbnail captions."),
      '#options' => $captionfield_options,
      '#default_value' => $this->options['captionfield'],
    );

    $form['flexslider']['id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Element ID'),
      '#description' => $this->t("Manually define the FlexSlider container ID attribute <em>Ensure you don't display similar ID elements on the same page</em>."),
      '#size' => 40,
      '#maxlength' => 255,
      '#default_value' => $this->options['id'],
    );

  }

  /**
   * {@inheritdoc}
   */
  function render() {

    // Group the rows according to the grouping field, if specified.
    $sets = parent::render();

    // Render each group separately and concatenate.
    $output = $sets;

    foreach ($sets as $key => &$set) {
      // Add caption field if chosen.
      if (!empty($this->options['captionfield'])) {
        $caption_field = $this->options['captionfield'];
        foreach ($set['#rows'] as $index => $row) {
          $set['#rows'][$index]['#caption'] = $this->rendered_fields[$index][$caption_field];
        }
      }
      $output[$key] = [
        '#theme' => $this->themeFunctions(),
        '#view' => $this->view,
        '#options' => $this->options,
        '#rows' => $set['#rows'],
        '#title' => $set['#title'],
      ];
    }

    return $output;
  }
}