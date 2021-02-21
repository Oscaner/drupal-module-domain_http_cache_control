<?php

namespace Drupal\domain_http_cache_control\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DomainHttpCacheController.
 *
 * @package Drupal\domain_http_cache_control\Controller
 */
class DomainHttpCacheController extends ControllerBase {

  /**
   * Enable the domain http cache control.
   *
   * @param string $domain_http_cache_control
   *   The domain http cache control name.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function enableEntity($domain_http_cache_control) {
    $entity = $this->entityTypeManager()->getStorage('domain_http_cache_control')->load($domain_http_cache_control);
    $entity->set('status', TRUE);
    $entity->save();

    $this->messenger()->addStatus($this->t('The @entity-type %label has been enabled.', [
      '@entity-type' => $entity->getEntityType()->getLowercaseLabel(),
      '%label' => $entity->label(),
    ]));

    return $this->redirect('entity.domain_http_cache_control.collection');
  }

  /**
   * Disable the domain http cache control.
   *
   * @param string $domain_http_cache_control
   *   The domain http cache control name.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function disableEntity($domain_http_cache_control) {
    $entity = $this->entityTypeManager()->getStorage('domain_http_cache_control')->load($domain_http_cache_control);
    $entity->set('status', FALSE);
    $entity->save();

    $this->messenger()->addStatus($this->t('The @entity-type %label has been disabled.', [
      '@entity-type' => $entity->getEntityType()->getLowercaseLabel(),
      '%label' => $entity->label(),
    ]));

    return $this->redirect('entity.domain_http_cache_control.collection');
  }

}
