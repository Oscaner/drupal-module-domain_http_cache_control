<?php

namespace Drupal\domain_http_cache_control\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface DomainHttpCacheControlInterface.
 *
 * @package Drupal\domain_http_cache_control
 */
interface DomainHttpCacheControlInterface extends ConfigEntityInterface {

  /**
   * Cached.
   *
   * @var string
   */
  const CACHE = 'cache';

  /**
   * Bypass.
   *
   * @var string
   */
  const BYPASS = 'bypass';

  /**
   * The page cache keys.
   *
   * @var array
   *
   * @see \Drupal\system\Form\PerformanceForm
   */
  const PAGE_CACHE_KEYS = ['page_cache_maximum_age'];

  /**
   * The http cache keys.
   *
   * @var array
   *
   * @see http_cache_control_form_system_performance_settings_alter()
   */
  const HTTP_CACHE_KEYS = ['http_max_age', '404_max_age', '302_max_age', '301_max_age', '5xx_max_age'];

  /**
   * Returns the domain pattern.
   *
   * @return string
   *   The domain pattern.
   */
  public function getDomainPattern();

  /**
   * Returns the weight.
   *
   * @return int
   *   The weight.
   */
  public function getWeight();

  /**
   * Set cache settings.
   *
   * @param string|null $key
   *   The key.
   * @param array $value
   *   The value.
   *
   * @return $this
   */
  public function setCacheSetting($key, array $value);

  /**
   * Returns the caches.
   *
   * @return array
   *   The cache settings.
   */
  public function getCacheSetting();

  /**
   * The page caches.
   *
   * @return array
   *   The page cache.
   */
  public function getPageCacheSetting();

  /**
   * The http caches.
   *
   * @return array
   *   The http cache.
   */
  public function getHttpCacheSetting();

  /**
   * Get page cache statue.
   *
   * @return bool
   *   The page cache statue.
   */
  public function getPageCacheStatue();

  /**
   * Get http cache statue.
   *
   * @return bool
   *   The http cache statue.
   */
  public function getHttpCacheStatue();

  /**
   * Get page cache keys.
   *
   * @return array
   */
  public function getPageCacheKeys();

  /**
   * Get http cache keys.
   *
   * @return array
   */
  public function getHttpCacheKeys();

}
