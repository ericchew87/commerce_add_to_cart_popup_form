<?php

namespace Drupal\commerce_add_to_cart_popup_form;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;

class AddToCartPopUpManager implements AddToCartPopUpManagerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * AddToAddToCartPopUpManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritDoc}
   */
  public function getAddToCartPopUpFormTitle(ProductInterface $product) {
    return $product->getTitle();
  }

  /**
   * {@inheritDoc}
   */
  public function buildAddToCartPopUpForm(ProductInterface $product) {
    $default_variation = $product->getDefaultVariation();

    $order_item_storage = $this->entityTypeManager->getStorage('commerce_order_item');
    $order_item = $order_item_storage->createFromPurchasableEntity($default_variation);
    /** @var \Drupal\commerce_cart\Form\AddToCartFormInterface $form_object */
    $form_object = $this->entityTypeManager->getFormObject('commerce_order_item', 'commerce_add_to_cart_popup_form');
    $form_object->setEntity($order_item);
    // The default form ID is based on the variation ID, but in this case the
    // product ID is more reliable (the default variation might change between
    // requests due to an availability change, for example).
    $form_object->setFormId($form_object->getBaseFormId() . '_commerce_product_' . $product->id());

    $modal_wrapper_id = 'commerce-add-to-cart-popup-form-modal-wrapper';

    $form_state = (new FormState())->setFormState([
      'product' => $product,
      'view_mode' => 'commerce_add_to_cart_popup_form',
      'settings' => [
        'combine' => FALSE,
      ],
      'modal_wrapper_id' => $modal_wrapper_id,
    ]);
    /** @var \Drupal\commerce_product\ProductViewBuilder $product_view_builder */
    $product_view_builder = $this->entityTypeManager->getViewBuilder('commerce_product');

    return [
      '#theme' => 'commerce_add_to_cart_popup_form',
      '#form' => $this->formBuilder->buildForm($form_object, $form_state),
      '#product' => $product_view_builder->view($product, 'commerce_add_to_cart_popup_form'),
      '#product_entity' => $product,
      '#order_item' => $order_item,
      '#prefix' => '<div id="' . $modal_wrapper_id . '">',
      '#suffix' => '</div>',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildAddToCartLink(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    assert($entity instanceof ProductInterface);
    if ($this->applies($build, $entity, $display, $view_mode)) {

      $settings = $display->getThirdPartySetting('commerce_add_to_cart_popup_form', 'settings');
      if (empty($settings['display'])) {
        $settings['display'] = [
          'width' => 800,
        ];
      }

      $build['commerce_add_to_cart_popup_form'] = [
        '#type' => 'link',
        '#title' => t('Add to cart'),
        '#url' => Url::fromRoute('commerce_add_to_cart_popup_form.add_to_cart_modal_form', [
          'commerce_product' => $entity->id(),
        ]),
        '#attributes' => [
          'class' => [
            'use-ajax',
            'button',
          ],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(array_filter($settings['display'])),
        ],
      ];
      $build['#attached']['library'][] = 'core/drupal.dialog.ajax';
    }
  }

  /**
   * {@inheritDoc}
   */
  public function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    if ($view_mode == 'commerce_add_to_cart_popup_form' && isset($build['variations'])) {
      unset($build['variations']);
    }
  }

  /**
   * @param array $build
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   * @param $view_mode
   *
   * @return bool
   */
  protected function applies(array $build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    return (bool) $display->getComponent('commerce_add_to_cart_popup_form');
  }

}
