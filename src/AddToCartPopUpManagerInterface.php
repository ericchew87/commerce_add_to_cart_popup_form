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
   * @param array $build
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   * @param $view_mode
   */
  public function buildAddToCartLink(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode);

  /**
   * @param array $build
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   * @param $view_mode
   */
  public function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode);
}
