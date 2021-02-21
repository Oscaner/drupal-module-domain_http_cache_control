<?php

namespace Drupal\domain_http_cache_control;

/**
 * Interface DomainHttpCacheControlManagerInterface.
 *
 * @package Drupal\domain_http_cache_control
 */
interface DomainHttpCacheControlManagerInterface {

  /**
   * The static cache key for domain http cache control.
   *
   * @var string
   */
  const STATIC_CACHE_KEY = 'domain_http_cache_control';

  /**
   * Get the valid domain http cache control entity.
   *
   * @param string $domain
   *   The domain or the entity id.
   *
   * @return \Drupal\domain_http_cache_control\Entity\DomainHttpCacheControlInterface|null
   *   The domain http cache control entity.
   */
  public function getValidDomainEntity(string $domain);

}
