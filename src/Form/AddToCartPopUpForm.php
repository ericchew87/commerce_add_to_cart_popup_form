<?php

namespace Drupal\commerce_add_to_cart_popup_form\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class AddToCartPopUpForm extends FormBase {

  /**
   * The product.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $product;

  /**
   * AddToCartPopUpForm constructor.
   */
  public function __construct() {
    $product = $this->getRouteMatch()->getParameter('commerce_product');
    if (!$product) {
      throw new MissingMandatoryParametersException('The commerce_product route parameter is required');
    }
    $this->product = $product;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'commerce_add_to_cart_popup_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product\ProductViewBuilder $product_view_builder */
    $product_view_builder = \Drupal::entityTypeManager()->getViewBuilder('commerce_product');
    $form['#prefix'] = '<div id="commerce_add_to_cart_popup_form">';
    $form['#suffix'] = '</div>';
    $form['product'] = $product_view_builder->view($this->product, 'commerce_add_to_cart_popup_form');

    return $form;
  }


  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  public static function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      $response->addCommand(new ReplaceCommand('#commerce_add_to_cart_popup_form', $form));
    }
    else {
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }


}
