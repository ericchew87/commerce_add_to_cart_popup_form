<?php

namespace Drupal\commerce_add_to_cart_popup_form;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;

interface AddToCartPopUpManagerInterface {

  /**
   * Gets the add to cart popup form title.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *
   * @return string
   *   The title.
   */
  public function getAddToCartPopUpFormTitle(ProductInterface $product);

  /**
   * Builds the add to cart popup form.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product.
   *
   * @return array
   *   The add to cart popup form.
   */
  public function buildAddToCartPopUpForm(ProductInterface $product);

  /**
   * Builds the add to cart link element.
   *
   * @param array $build
   *   The build array.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The product entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display
   * @param $view_mode
   *   The view mode.
   *
   * @return array
   *   The add to cart link render element.
   */
  public function buildAddToCartLink(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode);

}
