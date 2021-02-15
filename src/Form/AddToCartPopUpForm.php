<?php

namespace Drupal\commerce_add_to_cart_popup_form\Form;

use Drupal\commerce_add_to_cart_popup_form\Ajax\ReplaceCartBlockCommand;
use Drupal\commerce_cart\Form\AddToCartForm;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

class AddToCartPopUpForm extends AddToCartForm {

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $step = $form_state->get('step');
    if (!$step) {
      $step = 'cart';
      $form_state->set('step', $step);
    }

    if ($step == 'cart') {
      $form = parent::buildForm($form, $form_state);
      // Allows error messages to render when the form rebuilds with errors.
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -99,
      ];
    }
    elseif ($step == 'success') {
      $form = $this->buildSuccessForm($form, $form_state);
    }

    // Wrapper for the add to cart pop-up form.
    $wrapper_id = 'commerce-product-add-to-cart-popup-form';
    $form += [
      '#wrapper_id' => $wrapper_id,
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    // Wrapper for the entire modal.
    $form['#modal_wrapper_id'] = 'commerce-add-to-cart-popup-form-modal-wrapper';

    return $form;
  }

  /**
   * Builds the success form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The success form render array.
   */
  public function buildSuccessForm(array $form, FormStateInterface $form_state) {
    $form['success_message'] = [
      '#theme' => 'commerce_add_to_cart_popup_success',
      '#product_entity' => $form_state->get('product'),
      '#selected_variation' => $this->getSelectedVariation($form, $form_state),
      '#content' => [
        'status_messages' => [
          '#type' => 'status_messages'
        ]
      ],
    ];

    $actions = $this->actionsElement($form, $form_state);
    if ($actions) {
      $form['actions'] = $actions;
    }

    return $form;
  }

  /**
   * @inheritDoc
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $step = $form_state->get('step');
    if ($step == 'cart') {
      $actions = parent::actions($form, $form_state);
      $actions['submit']['#attributes']['class'][] = 'use-ajax';
      $actions['submit']['#ajax'] = [
        'callback' => [$this, 'ajaxSubmit'],
        'event' => 'click',
      ];
    }
    elseif ($step == 'success') {
      $actions['continue_shopping'] = [
        '#type' => 'button',
        '#value' => $this->t('Continue shopping'),
        '#attributes' => [
          'class' => ['user-ajax']
        ],
        '#ajax' => [
          'callback' => [$this, 'continueShoppingAjax'],
          'event' => 'click',
        ],
      ];
      $actions['view_cart'] = [
        '#type' => 'submit',
        '#value' => $this->t('View cart'),
        '#submit' => ['::viewCartSubmit'],
        '#button_type' => 'primary'
      ];
    }

    return $actions;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->set('step', 'success');
    $form_state->setRebuild(TRUE);
    parent::submitForm($form, $form_state);
  }

  /**
   * Ajax callback for the continue_shopping button element.
   *
   * Updates the cart block to display the updated count in
   * the cart and closes the modal.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function continueShoppingAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCartBlockCommand());
    $response->addCommand(new CloseModalDialogCommand());
    // The status messages are already displayed in the success form.
    $this->messenger()->deleteAll();

    return $response;
  }

  /**
   * Callback for the view_cart submit element.
   *
   * Redirects the user to the cart page.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function viewCartSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('commerce_cart.page');
  }

  /**
   * Ajax callback for the AddToCartPopUpForm submit element.
   *
   * If the form contains errors, the AddToCartForm will be rebuilt with the
   * included error messages.
   *
   * If the form submits successfully, the entire modal wrapper will
   * be replaced with the success form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      // Only replaces the AddToCartForm.
      $response->addCommand(new ReplaceCommand('#'.$form['#wrapper_id'], $form));
    }
    else {
      // Replaces the entire modal.
      $response->addCommand(new ReplaceCommand('#'.$form['#modal_wrapper_id'], $form));
    }

    return $response;
  }

  /**
   * Gets the selected variation.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface|null
   *  The selected variation, or NULL.
   */
  protected function getSelectedVariation(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product\ProductVariationStorageInterface $variation_storage */
    $variation_storage = $this->entityTypeManager->getStorage('commerce_product_variation');

    $selected_variation_id = $form_state->get('selected_variation');
    if (!$selected_variation_id) {
      $selected_variation_id = $form['purchased_entity']['widget'][0]['variation']['#value'];
    }
    if ($selected_variation_id) {
      $form_state->set('selected_variation', $selected_variation_id);
      return $variation_storage->load($selected_variation_id);
    }

    return NULL;
  }

}
