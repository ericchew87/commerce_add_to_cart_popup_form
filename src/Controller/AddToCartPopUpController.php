<?php


namespace Drupal\commerce_add_to_cart_popup_form\Controller;


use Drupal\commerce_add_to_cart_popup_form\AddToCartPopUpManagerInterface;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddToCartPopUpController extends ControllerBase {

  /**
   * The add to cart pop-up manager.
   *
   * @var \Drupal\commerce_add_to_cart_popup_form\AddToCartPopUpManagerInterface
   */
  protected $addToCartPopUpManager;

  /**
   * AddToCartPopUpController constructor.
   *
   * @param \Drupal\commerce_add_to_cart_popup_form\AddToCartPopUpManagerInterface $add_to_cart_popup_manager
   *   The add to cart popup manager.
   */
  public function __construct(AddToCartPopUpManagerInterface $add_to_cart_popup_manager) {
    $this->addToCartPopUpManager = $add_to_cart_popup_manager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_add_to_cart_popup_form.add_to_cart_popup_manager')
    );
  }

  /**
   * Builds the add to cart pop-up form.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $commerce_product
   *   The product.
   *
   * @return array
   *   A render array.
   */
  public function showAddToCartPopUp(ProductInterface $commerce_product) {
    return $this->addToCartPopUpManager->buildAddToCartPopUpForm($commerce_product);
  }

  /**
   * Title callback for the add to cart pop-up form route.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $commerce_product
   *   The product.
   *
   * @return string
   *   The title.
   */
  public function getTitle(ProductInterface $commerce_product) {
    return $this->addToCartPopUpManager->getAddToCartPopUpFormTitle($commerce_product);
  }

}
