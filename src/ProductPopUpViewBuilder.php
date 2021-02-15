<?php


namespace Drupal\commerce_add_to_cart_popup_form;


use Drupal\commerce_product\ProductViewBuilder;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductPopUpViewBuilder extends ProductViewBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('theme.registry'),
      $container->get('entity_display.repository'),
      $container->get('entity_type.manager'),
      // Use the variation popup field renderer.
      $container->get('commerce_add_to_cart_popup_form.variation_popup_field_renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    parent::alterBuild($build, $entity, $display, $view_mode);
    if ($view_mode == 'commerce_add_to_cart_popup_form' && !empty($build['variations'][0]['add_to_cart_form']['#lazy_builder'][0])) {
      $build['variations'][0]['add_to_cart_form']['#lazy_builder'][0] = 'commerce_add_to_cart_popup_form.lazy_builders:addToCartPopUpForm';
    }
  }

}
