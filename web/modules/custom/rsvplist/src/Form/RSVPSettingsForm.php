<?php

/**
 * @file
 * Contains the settings for administering the RSVP Form
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rsvplist_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rsvplist.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $types = node_type_get_names();

    // config() method is defined in ConfigFormBaseTrait, which is used in ConfigFormBase
    $config = $this->config('rsvplist.settings');

    $form['rsvplist_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('The content types to enable RSVP collection for'),
      '#default_value' => $config->get('allowed_types'),
      '#options' => $types,
      '#description' => $this->t('On the specified node types, an RSVP option will be available while the node is being edited.'),
    ];

    // We don't need to explicitly create a $form['submit'] because ConfigFormBase
    // (unlike FormBase) provides submit button config by default. But to utilize that,
    // we need to pass $form and $form_state back up to the buildForm method 
    // of ConfigFormBase, hence the line below
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected_allowed_types = array_filter($form_state->getValue('rsvplist_types'));
    sort($selected_allowed_types);

    $this->config('rsvplist.settings') //load the config object
      ->set('allowed_types', $selected_allowed_types)
      ->save();

    parent::submitForm($form, $form_state);
  }
}