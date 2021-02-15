<?php

namespace Drupal\commerce_add_to_cart_popup_form;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
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
    /** @var \Drupal\commerce_add_to_cart_popup_form\ProductPopUpViewBuilder $product_popup_view_builder */
    $product_popup_view_builder = $this->entityTypeManager->getHandler('commerce_product', 'popup_view_builder');
    return [
      '#theme' => 'commerce_add_to_cart_popup_form',
      '#product' => $product_popup_view_builder->view($product, 'commerce_add_to_cart_popup_form'),
      '#product_entity' => $product,
      '#attributes' => [
        'id' => 'commerce-add-to-cart-popup-form-modal-wrapper',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function buildAddToCartLink(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    assert($entity instanceof ProductInterface);
    if ($this->shouldRender($build, $entity, $display, $view_mode)) {

      $settings = $display->getThirdPartySetting('commerce_add_to_cart_popup_form', 'field_settings');
      if (empty($settings['display'])) {
        $settings['display'] = [
          'width' => 800,
        ];
      }
      $build['#attached']['library'][] = 'core/drupal.dialog.ajax';

      $link = [
        '#type' => 'link',
        '#title' => t('Add to cart'),
        '#url' => Url::fromRoute('commerce_add_to_cart_popup_form.add_to_cart_modal_form', [
          'commerce_product' => $entity->id(),
        ]),
        '#attributes' => [
          'rel' => 'nofollow',
          'class' => [
            'use-ajax',
          ],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode(array_filter($settings['display'])),
        ],
      ];

      $build['commerce_add_to_cart_popup_form'] = [
        '#theme' => 'commerce_add_to_cart_popup_link',
        '#link' => $link,
        '#product_entity' => $entity,
        '#view_mode' => $view_mode,
        '#display' => $display,
      ];
    }
  }

  /**
   * Gets whether the pop-up link should render.
   *
   * @param array $build
   *   The build.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The product entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display.
   * @param $view_mode
   *   The view mode.
   *
   * @return bool
   *   TRUE if the pop-up link should render, FALSE otherwise.
   */
  protected function shouldRender(array $build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    // The add to cart popup form field must be present in the display.
    if (!$display->getComponent('commerce_add_to_cart_popup_form')) {
      return FALSE;
    }

    // The display ID for the pop-up display mode.
    $popup_display_id = $entity->getEntityTypeId() . '.' . $entity->bundle() . '.commerce_add_to_cart_popup_form';
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $add_to_cart_popup_form_view_display */
    $add_to_cart_popup_form_view_display = EntityViewDisplay::load($popup_display_id);
    // The display mode for the pop-up must be present.
    if (!$add_to_cart_popup_form_view_display) {
      return FALSE;
    }

    // The add to cart form must be present in the pop-up display.
    $variation_component = $add_to_cart_popup_form_view_display->getComponent('variations');
    if (!$variation_component || $variation_component['type'] !== 'commerce_add_to_cart') {
      return FALSE;
    }

    return TRUE;
  }

}
