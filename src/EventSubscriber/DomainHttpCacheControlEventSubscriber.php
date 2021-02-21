<?php

namespace Drupal\domain_http_cache_control\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\domain_http_cache_control\DomainHttpCacheControlManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class DomainHttpCacheControlEventSubscriber.
 *
 * @package Drupal\domain_http_cache_control\EventSubscriber
 */
class DomainHttpCacheControlEventSubscriber implements EventSubscriberInterface {

  /**
   * The domain http cache control manager.
   *
   * @var \Drupal\domain_http_cache_control\DomainHttpCacheControlManagerInterface
   */
  protected $domainHttpCacheControlManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * DomainHttpCacheControlEventSubscriber constructor.
   *
   * @param \Drupal\domain_http_cache_control\DomainHttpCacheControlManagerInterface $domain_http_cache_control_manager
   */
  public function __construct(DomainHttpCacheControlManagerInterface $domain_http_cache_control_manager, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->domainHttpCacheControlManager = $domain_http_cache_control_manager;
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Set domain http cache control headers.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *
   * @see \Drupal\http_cache_control\EventSubscriber\CacheControlEventSubscriber::setHeaderCacheControl()
   */
  public function setHeaderCacheControl(FilterResponseEvent $event) {
    $response = $event->getResponse();

    // No action if the response not cacheable.
    if (!$response->isCacheable()) {
      return;
    }

    // Get valid domain entity.
    $request = $event->getRequest();
    $domain = parse_url($request->getSchemeAndHttpHost())['host'] ?? NULL;
    $domain_entity = $this->domainHttpCacheControlManager->getValidDomainEntity($domain);

    // No action if not have related domain entity.
    if (!$domain_entity) {
      return;
    }

    // Cache settings.
    $cache = $domain_entity->getCacheSetting();

    $max_age = $response->getMaxAge();

    switch ($response->getStatusCode()) {
      case 404:
        $ttl = $cache['http']['404_max_age'] ?? $max_age;
        break;

      case 302:
        $ttl = $cache['http']['302_max_age'] ?? $max_age;
        break;

      case 301:
        $ttl = $cache['http']['301_max_age'] ?? $max_age;
        break;

      default:
        $ttl = $cache['page']['page_cache_maximum_age'] ?? $max_age;
        break;
    }

    // Set 5xx max age.
    if ($response->getStatusCode() >= 500) {
      $ttl = $cache['http']['5xx_max_age'] ?? $max_age;
    }

    // If shield enabled, set the ttl to zero.
    // @see \Drupal\shield\ShieldMiddleware::handle()
    if ($this->moduleHandler->moduleExists('shield')) {
      $config = $this->configFactory->get('shield.settings');
      $user = NULL;

      switch ($config->get('credential_provider')) {
        case 'shield':
          $user = $config->get('credentials.shield.user');
          break;

        case 'key':
          $user = $config->get('credentials.key.user');
          break;

        case 'multikey':
          $user_pass_key = $this->entityTypeManager->getStorage('key')->load($config->get('credentials.multikey.user_pass_key'));
          if ($user_pass_key) {
            $user = $user_pass_key->getKeyValues()['username'] ?? $user;
          }
          break;
      }

      // If shield enabled, set the ttl age to zero.
      if ($config->get('shield_enable') && !empty($user)) {
        $ttl = 0;
      }
    }

    $this->moduleHandler->alter('domain_http_cache_control_ttl', $response, $domain_entity, $ttl);

    $response->setSharedMaxAge($ttl);
    $response->setClientTtl(($cache['http']['http_max_age'] ?? 0));
    $response->headers->set('Domain-Http-Cache-Control', $domain_entity->label());
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\http_cache_control\EventSubscriber\CacheControlEventSubscriber::getSubscribedEvents()
   */
  public static function getSubscribedEvents() {
    // Response: set header content for security policy.
    $events[KernelEvents::RESPONSE][] = ['setHeaderCacheControl', -20];
    return $events;
  }

}
