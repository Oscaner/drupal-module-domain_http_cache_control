<?php

namespace Drupal\domain_http_cache_control\Entity;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Annotation\ConfigEntityType;
use Drupal\domain_http_cache_control\DomainHttpCacheControlManagerInterface;

/**
 * Configuration entity for the domain http cache control.
 *
 * @ConfigEntityType(
 *   id = "domain_http_cache_control",
 *   label = @Translation("Domain Http Cache Control"),
 *   config_prefix = "domain_http_cache_control",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "domain"
 *   },
 *   handlers = {
 *     "access" = "Drupal\domain_http_cache_control\DomainHttpCacheAccessControlHandler",
 *     "list_builder" = "Drupal\domain_http_cache_control\DomainHttpCacheControlListBuilder",
 *     "form" = {
 *       "add" = "Drupal\domain_http_cache_control\Form\DomainHttpCacheControlContentForm",
 *       "edit" = "Drupal\domain_http_cache_control\Form\DomainHttpCacheControlContentForm",
 *       "delete" = "Drupal\domain_http_cache_control\Form\DomainHttpCacheControlContentDeleteForm"
 *     }
 *   },
 *   links = {
 *     "collection" = "/admin/config/development/domain-http-cache",
 *     "canonical" = "/admin/config/development/domain-http-cache/manage/{domain_http_cache_control}",
 *     "edit-form" = "/admin/config/development/domain-http-cache/manage/{domain_http_cache_control}/edit",
 *     "delete-form" = "/admin/config/development/domain-http-cache/manage/{domain_http_cache_control}/delete",
 *     "enable" = "/admin/config/development/domain-http-cache/manage/{domain_http_cache_control}/enable",
 *     "disable" = "/admin/config/development/domain-http-cache/manage/{domain_http_cache_control}/disable"
 *   },
 *   config_export = {
 *     "id",
 *     "status",
 *     "weight",
 *     "domain_pattern",
 *     "cache"
 *   }
 * )
 */
class DomainHttpCacheControl extends ConfigEntityBase implements DomainHttpCacheControlInterface {

  /**
   * The domain id.
   *
   * @var string
   */
  protected $id;

  /**
   * The status.
   *
   * @var bool
   */
  protected $status = TRUE;

  /**
   * The weight.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * The domain.
   *
   * @var string
   */
  protected $domain_pattern;

  /**
   * The Cache settings.
   *
   * @var array
   */
  protected $cache;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getDomainPattern();
  }

  /**
   * {@inheritdoc}
   */
  public function getDomainPattern() {
    return $this->domain_pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setCacheSetting($key, $value) {
    if ($key) {
      $this->cache[$key] = $value;
    }
    else {
      $this->cache = $value;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSetting() {
    return $this->cache;
  }

  /**
   * {@inheritdoc}
   */
  public function getPageCacheSetting() {
    return $this->getCacheSetting()['page'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getHttpCacheSetting() {
    return $this->getCacheSetting()['http'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPageCacheStatue() {
    foreach ($this->getPageCacheSetting() as $age) {
      if ((int) $age > 0) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getHttpCacheStatue() {
    foreach ($this->getHttpCacheSetting() as $age) {
      if ((int) $age > 0) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPageCacheKeys() {
    $page_cache_keys = self::PAGE_CACHE_KEYS;
    $this->moduleHandler()->alter('domain_http_page_cache_keys', $page_cache_keys);
    return $page_cache_keys;
  }

  /**
   * {@inheritdoc}
   */
  public function getHttpCacheKeys() {
    $http_cache_keys = self::HTTP_CACHE_KEYS;
    $this->moduleHandler()->alter('domain_http_http_cache_keys', $http_cache_keys);
    return $http_cache_keys;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    // TODO: Clear cache for domain entity mapping.
    // @see \Drupal\domain_http_cache_control\DomainHttpCacheControlManager::getValidDomainEntity()
    return parent::save();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), $this->getCacheTagsToInvalidate());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    return Cache::mergeTags(parent::getCacheTagsToInvalidate(), ['http_response']);
  }

}
