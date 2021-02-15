<?php

namespace Drupal\commerce_add_to_cart_popup_form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_add_to_cart_popup_form\VariationPopUpFieldRenderer;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;

class ProductVariationAjaxSubscriber implements EventSubscriberInterface {

  /**
   * The variation pop-up field renderer.
   *
   * @var \Drupal\commerce_add_to_cart_popup_form\VariationPopUpFieldRenderer
   */
  protected $variationPopUpFieldRenderer;

  /**
   * ProductVariationAjaxSubscriber constructor.
   *
   * @param \Drupal\commerce_add_to_cart_popup_form\VariationPopUpFieldRenderer $variation_popup_field_renderer
   *   The variation pop-up field renderer.
   */
  public function __construct(VariationPopUpFieldRenderer $variation_popup_field_renderer) {
    $this->variationPopUpFieldRenderer = $variation_popup_field_renderer;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [
      ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE => 'onProductVariationAjaxChange',
    ];
  }

  /**
   * Callback for ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE
   *
   * @param \Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent $event
   *   The product variation ajax change event.
   */
  public function onProductVariationAjaxChange(ProductVariationAjaxChangeEvent $event) {
    $view_mode = $event->getViewMode();
    if ($view_mode == 'commerce_add_to_cart_popup_form') {
      $variation = $event->getProductVariation();
      $response = $event->getResponse();

      // Remove the commands added by the default variation renderer.
      // This is done to ensure that only fields rendered in the pop-up are replaced.
      $commands = &$response->getCommands();
      foreach ($commands as $key => $command) {
        if (isset($command['selector']) && str_contains($command['selector'], '.product--variation-field')) {
          unset($commands[$key]);
        }
      }
      // Add the ajax replacement commands for the variation fields in the pop-up form.
      $this->variationPopUpFieldRenderer->replaceRenderedFields($response, $variation, $view_mode);
    }
  }

}
