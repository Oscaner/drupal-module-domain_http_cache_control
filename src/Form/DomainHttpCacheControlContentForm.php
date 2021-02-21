<?php

namespace Drupal\domain_http_cache_control\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\system\Form\PerformanceForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DomainHttpCacheControlContentForm.
 *
 * @package Drupal\domain_http_cache_control\Form
 */
class DomainHttpCacheControlContentForm extends EntityForm implements ContainerInjectionInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * DomainHttpCacheControlContentForm constructor.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   */
  public function __construct(FormBuilderInterface $form_builder, MessengerInterface $messenger) {
    $this->formBuilder = $form_builder;
    $this->messenger = $messenger;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\domain_http_cache_control\Form\DomainHttpCacheControlContentForm|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['domain_http_cache_control.domain_http_cache_control.' . $this->entity->id()];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\domain_http_cache_control\Entity\DomainHttpCacheControlInterface $cache_entity */
    $cache_entity = $this->entity;

    $form['domain_pattern'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Domain Pattern'),
      '#maxlength' => 255,
      '#default_value' => $cache_entity->getDomainPattern(),
      '#description' => $this->t('The domain pattern, allow regular expression match. (E.g. (.*)\.domain\.com)'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cache_entity->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'source' => ['domain_pattern'],
        'exists' => ['Drupal\domain_http_cache_control\Entity\DomainHttpCacheControl', 'load'],
      ],
      '#disabled' => !$cache_entity->isNew(),
    ];

    $form['weight'] = [
      '#type' => 'number',
      '#title' => $this->t('Weight'),
      '#description' => $this->t('The weight to order the domain http cache control entities.'),
      '#default_value' => $cache_entity->getWeight(),
    ];

    $form['caching'] = $this->getPerformanceCachingForm($form, $form_state);

    return $form;
  }

  /**
   * Get performance caching form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array.
   */
  public function getPerformanceCachingForm(array $form, FormStateInterface $form_state) {
    // Get performance form.
    $performance_form = $this->formBuilder->getForm(PerformanceForm::class);

    // Not have caching form.
    if (!isset($performance_form['caching'])) {
      return [];
    }

    $caching_form = [
      '#type' => $performance_form['caching']['#type'] ?? 'details',
      '#title' => $performance_form['caching']['#title'] ?? $this->t('Caching'),
      '#open' => $performance_form['caching']['#open'] ?? TRUE,
      '#required' => $performance_form['caching']['#required'] ?? FALSE,
      '#title_display' => $performance_form['caching']['#title_display'] ?? 'before',
      '#description_display' => $performance_form['caching']['#description_display'] ?? 'after',
    ];

    // Entity.
    /** @var \Drupal\domain_http_cache_control\Entity\DomainHttpCacheControlInterface $entity */
    $entity = $this->entity;
    // Get user input values.
    $values = $form_state->getUserInput();
    // Just get all fields.
    foreach (Element::children($performance_form['caching']) as $field_name) {
      $caching_form[$field_name] = $performance_form['caching'][$field_name];
      // Group: page cache.
      if (in_array($field_name, $entity->getPageCacheKeys())) {
        if (isset($entity->getPageCacheSetting()[$field_name])) {
          $caching_form[$field_name]['#default_value'] = $entity->getPageCacheSetting()[$field_name];
          $caching_form[$field_name]['#value'] = $entity->getPageCacheSetting()[$field_name];
        }
        $caching_form[$field_name]['#parents'] = ['cache', 'page', $field_name];
      }
      // Group: http cache.
      elseif (in_array($field_name, $entity->getHttpCacheKeys())) {
        if (isset($entity->getHttpCacheSetting()[$field_name])) {
          $caching_form[$field_name]['#default_value'] = $entity->getHttpCacheSetting()[$field_name];
          $caching_form[$field_name]['#value'] = $entity->getHttpCacheSetting()[$field_name];
        }
        $caching_form[$field_name]['#parents'] = ['cache', 'http', $field_name];
      }
      // Group: none.
      elseif (isset($entity->getCacheSetting()[$field_name])) {
        if (isset($entity->getCacheSetting()[$field_name])) {
          $caching_form[$field_name]['#default_value'] = $entity->getCacheSetting()[$field_name];
          $caching_form[$field_name]['#value'] = $entity->getCacheSetting()[$field_name];
        }
        $caching_form[$field_name]['#parents'] = ['cache', $field_name];
      }
      // Save operation will trigger this function again, need override the field value from user input.
      if (isset($values[$field_name])) {
        $caching_form[$field_name]['#value'] = $values[$field_name];
      }
    }

    return $caching_form;
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    // No actions if there are no form.
    return isset($form['domain_pattern']) ? parent::actions($form, $form_state) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Set cache value.
    $this->entity->setCacheSetting(NULL, ($values['cache'] ?? []));

    $form_state->setRedirect('entity.domain_http_cache_control.collection');

    $return = parent::save($form, $form_state);

    $this->messenger->addStatus($this->t('The @entity-type %label has been saved.', [
      '@entity-type' => $this->entity->getEntityType()->getLowercaseLabel(),
      '%label' => $this->entity->label(),
    ]));

    return $return;
  }

}
