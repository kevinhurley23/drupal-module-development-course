<?php

/**
 * @file
 * Creates a block which displays the RSVPForm contained in RSVPForm.php
 */

namespace Drupal\rsvplist\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * @Block(
 *   id = "rsvp_block",
 *   admin_label = @Translation("RSVP Form Block")
 * )
 */
class RSVPBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\rsvplist\Form\RSVPForm');
  }

  public function blockAccess(AccountInterface $account) {
    // If viewing a node, get the fully loaded node object.
    $node = \Drupal::routeMatch()->getParameter('node');

    if (!(is_null($node))) { // is the current route a node?
      $enabler = \Drupal::service('rsvplist.enabler'); // modules/custom/rsvplist/src/EnablerService.php

      if ($enabler->isEnabled($node)) { // is this node RSVP enabled?
        return AccessResult::allowedIfHasPermission($account, 'view rsvplist'); // does the user have permission to view the block?
      }
    }

    // Fallback if the current route is not a node
    return AccessResult::forbidden();
  }
}