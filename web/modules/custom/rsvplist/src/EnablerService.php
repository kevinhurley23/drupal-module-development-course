<?php

namespace Drupal\rsvplist;

use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;

class EnablerService {
  protected $database_connection;

  public function __construct(Connection $connection) {
    $this->database_connection = $connection;
  }

  /**
   * Checks if an individual node is RSVP enabled.
   * @param Node $node
   * @return bool
   */
  public function isEnabled(Node &$node) {
    if ($node->isNew()) {
      return FALSE;
    }

    try {
      $select = $this->database_connection->select('rsvplist_enabled', 're');
      $select->fields('re', ['nid']); // select 'nid' from rsvplist_enabled
      $select->condition('nid', $node->id()); // where 'nid' = $node->id()
      $results = $select->execute();

      return !(empty($results->fetchCol())); // return TRUE if $results is not empty
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(
        t('Unable to determine RSVP settings at this time. Please try again.')
      );
      return NULL;
    }
  }

  /**
   * Sets an individual node to be RSVP enabled.
   * @param Node $node
   * @return void
   */
  public function setEnabled(Node $node) {
    try {
      if (!($this->isEnabled($node))) { // If the node is not already enabled
        $insert = $this->database_connection->insert('rsvplist_enabled');
        $insert->fields(['nid']);
        $insert->values([$node->id()]);
        $insert->execute();
      }
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(
        t('Unable to save RSVP settings at this time. Please try again.')
      );
    }
  }

  /**
   * Sets an individual node to be NOT RSVP enabled
   * @param Node $node
   * @return void
   */
  public function delEnabled(Node $node) {
    try {
      $delete = $this->database_connection->delete('rsvplist_enabled');
      $delete->condition('nid', $node->id());
      $delete->execute();
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError(
        t('Unable to save RSVP settings at this time. Please try again.')
      );
    }
  }
}