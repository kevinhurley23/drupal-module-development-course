<?php

/**
 * @file
 * Provide site admins with a list of all the RSVP List signups
 * so they know who is attending their events.
 */

namespace Drupal\rsvplist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class ReportController extends ControllerBase {
  /**
   * Gets and returns all RSVPs for all nodes.
   * These are returned as an associative array, with each row
   * containing the username, the node title, and email of RSVP.
   * 
   * @return array|null
   */
  protected function load() {
    try {
      $database = \Drupal::database();
      $select_query = $database->select('rsvplist', 'r') // 'r' is an alias for the 'rsvplist' table, which is used below

      // the 'rsvplist' table only contains uid of a user and nid of a node.
      // we want the username and node title, so we need to also get data from
      // users_field_data and node_field_data
      // Join the user table, so we can get the entry creator's username.
      $select_query->join('users_field_data', 'u', 'r.uid = u.uid');
      // Join the node table, so we can get the event's name.
      $select_query->join('node_field_data', 'n', 'r.nid = n.nid');

      // We don't need every column from all three tables
      // Select these spcific fields for the output
      $select_query->addField('u', 'name', 'username');
      $select_query->addField('n', 'title');
      $select_query->addField('r', 'mail');

      /* $select_query translated to SQL:
      SELECT u.name AS username, n.title, r.mail
      FROM {rsvplist} r
      INNER JOIN {users_field_data} u ON r.uid = u.uid
      INNER JOIN {node_field_data} n ON r.nid = n.nid
      */

      // Execute query and fetch records as an associative array
      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);

      return $entries;
    } catch (\Exception $e) {
      // Display an error message
      \Drupal::messenger()->addStatus(
        t('Unable to access the database at this time. Please try again later.')
      );
      return NULL;
    }
  }

  /**
   * Calls load() and creates the RSVPList report page from the results.
   * 
   * @return array
   *  Render array for the RSVPList report output.
   */
  public function report() {
    $content = []; // Initialize render array

    $content['message'] = [
      '#markup' => t('Below is a list of all Event RSVPs including username, email address and the name of the event they will be attending.')
    ];

    // Table headers
    $headers = [
      t('Username'),
      t('Event'),
      t('Email')
    ];

    $table_rows = $this->load();

    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $table_rows,
      '#empty' => t('No RSVPs found.')
    ]

    // Disable caching for this page
    $content['#cache']['max-age'] = 0;

    return $content;
  }
}