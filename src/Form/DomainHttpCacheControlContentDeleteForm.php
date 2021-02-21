<?php

namespace Drupal\domain_http_cache_control\Form;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Entity\EntityDeleteFormTrait;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DomainHttpCacheControlContentDeleteForm.
 *
 * @package Drupal\domain_http_cache_control\Form
 */
class DomainHttpCacheControlContentDeleteForm extends EntityConfirmFormBase {

  use EntityDeleteFormTrait {
    submitForm as traitSubmitForm;
  }

  /**
   * The config manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * DomainHttpCacheControlContentDeleteForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The config manager.
   */
  public function __construct(ConfigManagerInterface $config_manager) {
    $this->configManager = $config_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\domain_http_cache_control\DomainHttpCacheInterface $entity */
    $entity = $this->getEntity();

    $this->addDependencyListsToForm($form, $entity->getConfigDependencyKey(), [$entity->getConfigDependencyName()], $this->configManager, $this->entityTypeManager);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->traitSubmitForm($form, $form_state);
  }

}
