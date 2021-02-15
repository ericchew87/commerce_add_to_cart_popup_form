<?php


namespace Drupal\commerce_add_to_cart_popup_form;


use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_product\ProductVariationFieldRenderer;

class VariationPopUpFieldRenderer extends ProductVariationFieldRenderer {

  /**
   * {@inheritDoc}
   */
  protected function buildAjaxReplacementClass($field_name, ProductVariationInterface $variation) {
    // Modify the ajax class to ensure only pop-up fields are replaced.
    return 'product--variation-popup-field--variation_' . $field_name . '__' . $variation->getProductId();
  }

}
