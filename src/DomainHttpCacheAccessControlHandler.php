<?php

namespace Drupal\domain_http_cache_control;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class DomainHttpCacheAccessControlHandler.
 *
 * @package Drupal\domain_http_cache_control
 */
class DomainHttpCacheAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    return AccessResult::allowedIf($account->hasPermission('administer domain http cache control'));
  }

}
