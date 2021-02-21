<?php

namespace Drupal\domain_http_cache_control;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\domain_http_cache_control\Entity\DomainHttpCacheControlInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class DomainHttpCacheControlListBuilder.
 *
 * @package Drupal\domain_http_cache_control
 */
class DomainHttpCacheControlListBuilder extends ConfigEntityListBuilder {

  /**
   * The config factory that knows what is overwritten.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The domain http cache control manager.
   *
   * @var \Drupal\domain_http_cache_control\DomainHttpCacheControlManagerInterface
   */
  protected $domainHttpCacheControlManager;

  /**
   * The current domain.
   *
   * @var string
   */
  protected $currentDomain;

  /**
   * The valid domain entity.
   *
   * @var \Drupal\domain_http_cache_control\Entity\DomainHttpCacheControlInterface
   */
  protected $validDomainEntity;

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, ConfigFactoryInterface $config_factory, RequestStack $request_stack, DomainHttpCacheControlManagerInterface $domain_http_cache_control_manager) {
    parent::__construct($entity_type, $storage);
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->domainHttpCacheControlManager = $domain_http_cache_control_manager;
    $host = parse_url($this->requestStack->getCurrentRequest()->getSchemeAndHttpHost());
    $this->currentDomain = $host['host'] ?? NULL;
    $this->validDomainEntity = $this->domainHttpCacheControlManager->getValidDomainEntity($this->currentDomain);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('domain_http_cache_control.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['domain_pattern'] = $this->t('Domain Pattern');
    $header['id'] = $this->t('Machine name');
    $header['page_cache'] = $this->t('Page cache');
    $header['http_cache'] = $this->t('Http cache');
    $header['status'] = $this->t('Status');
    $header['current'] = $this->t('Current');
    $header['weight'] = $this->t('Weight');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['domain_pattern'] = $entity->label();
    $row['id'] = $entity->id();
    $row['page_cache'] = DomainHttpCacheControlInterface::BYPASS;
    $row['http_cache'] = DomainHttpCacheControlInterface::BYPASS;
    if ($entity instanceof DomainHttpCacheControlInterface) {
      $row['page_cache'] = $entity->getPageCacheStatue() ? DomainHttpCacheControlInterface::CACHE : DomainHttpCacheControlInterface::BYPASS;
      $row['http_cache'] = $entity->getHttpCacheStatue() ? DomainHttpCacheControlInterface::CACHE : DomainHttpCacheControlInterface::BYPASS;
    }
    $row['status'] = $entity->status() ? 'Enabled' : 'Disabled';
    $row['current'] = $this->validDomainEntity && $this->validDomainEntity->id() === $entity->id() ? '√' : '';
    $row['weight'] = $entity->getWeight();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\domain_http_cache_control\Entity\DomainHttpCacheControlInterface $entity */
    $operations = parent::getDefaultOperations($entity);

    // Show enable button, if the entity disabled.
    if (!$entity->get('status') && $entity->hasLinkTemplate('enable')) {
      $operations['enable'] = [
        'title' => t('Enable'),
        'weight' => 40,
        'url' => $entity->toUrl('enable'),
      ];
    }
    // Show disable button, if the entity enabled.
    elseif ($entity->hasLinkTemplate('disable')) {
      $operations['disable'] = [
        'title' => t('Disable'),
        'weight' => 50,
        'url' => $entity->toUrl('disable'),
      ];
    }

    // No delete or disable for default entity.
    if ($entity->id() === 'default') {
      unset($operations['delete']);
      unset($operations['disable']);
    }

    return $operations;
  }

}
