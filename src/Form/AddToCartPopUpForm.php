<?php

namespace Drupal\commerce_add_to_cart_popup_form\Form;

use Drupal\commerce_cart\Form\AddToCartForm;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AddToCartPopUpForm extends AddToCartForm {

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $step = $form_state->get('step') ?? 'cart';

    if ($step == 'cart') {
      $form = parent::buildForm($form, $form_state);
    }
    else {
      $form = $this->buildSuccessForm($form, $form_state);
    }

    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -99,
    ];

    $wrapper_id = 'commerce-product-add-to-cart-popup-form-ajax-wrapper';
    $form['#wrapper_id'] = $wrapper_id;
    $form['#modal_wrapper_id'] = $form_state->get('modal_wrapper_id');
    $form['#prefix'] = '<div id="' . $wrapper_id . '">';
    $form['#suffix'] = '</div>';

    return $form;
  }

  public function buildSuccessForm(array $form, FormStateInterface $form_state) {
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue shopping'),
      '#submit' => ['::continueShoppingSubmit'],
      '#button_type' => 'primary'
    ];
    $form['actions']['cart'] = [
      '#type' => 'submit',
      '#value' => $this->t('View cart'),
      '#submit' => ['::viewCartSubmit'],
      '#button_type' => 'primary'
    ];

    return $form;
  }

  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#attributes']['class'][] = 'use-ajax';
    $actions['submit']['#ajax'] = [
      'callback' => [$this, 'ajaxSubmit'],
      'event' => 'click',
    ];

    return $actions;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->set('step', 'success');
    $form_state->setRebuild(TRUE);
    parent::submitForm($form, $form_state);
  }

  public function continueShoppingSubmit(array &$form, FormStateInterface $form_state) {
    $referer = \Drupal::request()->headers->get('referer');
    $form_state->setRedirectUrl(Url::fromUri($referer));
  }

  public function viewCartSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('commerce_cart.page');
  }

  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      $response->addCommand(new ReplaceCommand('#'.$form['#wrapper_id'], $form));
    }
    else {
      $response->addCommand(new ReplaceCommand('#'.$form['#modal_wrapper_id'], $form));
    }

    return $response;
  }

  public function closeModal(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    $this->messenger()->deleteAll();

    return $response;
  }



}
