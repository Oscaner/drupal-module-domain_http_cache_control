<?php

namespace Drupal\domain_http_cache_control;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\domain_http_cache_control\Entity\DomainHttpCacheControl;

/**
 * Class DomainHttpCacheControlManager.
 *
 * @package Drupal\domain_http_cache_control
 */
class DomainHttpCacheControlManager implements DomainHttpCacheControlManagerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * DomainHttpCacheControlManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getSortedDomainEntities() {
    $entities = $this->entityTypeManager->getStorage('domain_http_cache_control')->loadMultiple();
    // Sort the entities using the entity class's sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    uasort($entities, [DomainHttpCacheControl::class, 'sort']);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidDomainEntity(string $domain) {
    // TODO: Cache for domain entity mapping.
    $entity = NULL;

    $sorted_entities = $this->getSortedDomainEntities();

    foreach ($sorted_entities as $domain_entity) {
      // Disabled.
      if (!$domain_entity->status()) {
        continue;
      }

      // Pattern matched, exit loop.
      $domain_pattern = $domain_entity->getDomainPattern();
      if (preg_match("/$domain_pattern/", $domain)) {
        $entity = $domain_entity;
        break;
      }
    }

    return $entity;
  }

}
