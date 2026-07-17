<?php

/**
 * @file
 * A form to collect an email address for RSVP details.
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rsvplist_email_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Attempt to get the fully loaded node object of the viewed page.
    $node = \Drupal::routeMatch()->getParameter('node');

    if (!(is_null($node))) {
      $nid = $node->id();
    } else {
      $nid = 0;
    }

    // Establish the $form render array. It has an email test field,
    // a submit button, and a field containing the node ID.
    $form['email'] = [
      '#type' => 'textfield',
      '#title' => t('Email Address'),
      '#size' => 25,
      '#description' => t('We will send updates to the email address you provide'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('RSVP'),
    ];
    $form['nid'] = [
      '#type' => 'hidden',
      '#value' => $nid,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('email');
    if ( !(\Drupal::service('email.validator')->isValid($value)) ) {
      $form_state->setErrorByName('email',
        // Using % instead of @ means Drupal will wrap 'mail' in <em> when displaying the message.
        $this->t("It appears that %mail is not a valid email. Please try again", ['%mail' => $value])
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $form_state is the status of the values entered into the form at the time of submission
    // $submitted_email = $form_state->getValue('email');
    // $this->messenger()->addMessage(t("The form is working! You entered @entry.",
    //   ['@entry' => $submitted_email]));

    try {
      // Initiate variables to save
      $uid = \Drupal::currentUser()->id();
      $nid = $form_state->getValue('nid');
      $email = $form_state->getValue('email');
      $current_time = \Drupal::time()->getCurrentTime();

      // Insert values into the DB with a dynamic query
      $query = \Drupal::database()->insert('rsvplist');
      $query->fields([
        'uid',
        'nid',
        'mail',
        'created',
      ]);
      $query->values([
        $uid,
        $nid,
        $email,
        $current_time,
      ]);
      $query->execute();

      // Display a message to the user
      \Drupal::messenger()->addMessage(
        t("Thanks for your RSVP! You will receive updates at @email.", ['@email' => $email])
      );
    } catch (\Exception $e) {
      \Drupal::messenger()->addMessage(
        t("There was a problem with your RSVP. Please try again later.")
      );
      \Drupal::logger('rsvplist')->error($e->getMessage());
    }
  }
}