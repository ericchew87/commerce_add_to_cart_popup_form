<?php

namespace Drupal\commerce_add_to_cart_popup_form\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class ReplaceCartBlockCommand implements CommandInterface {

  /**
   * A CSS selector string.
   *
   * If the command is a response to a request from an #ajax form element then
   * this value can be NULL.
   *
   * @var string
   */
  protected $selector;

  /**
   * A settings array to be passed to any attached JavaScript behavior.
   *
   * @var array
   */
  protected $settings;

  /**
   * Constructs an InsertCommand object.
   *
   * @param string $selector
   *   A CSS selector.
   * @param array $settings
   *   An array of JavaScript settings to be passed to any attached behaviors.
   */
  public function __construct($selector = '.cart--cart-block', array $settings = NULL) {
    $this->selector = $selector;
    $this->settings = $settings;
  }

  /**
   * @inheritDoc
   */
  public function render() {
    $build = [];

    /** @var \Drupal\Core\Block\BlockManagerInterface $block_manager */
    $block_manager = \Drupal::service('plugin.manager.block');
    $cart_block = $block_manager->createInstance('commerce_cart', []);
    if ($cart_block) {
      $build = $cart_block->build();
    }

    return [
      'command' => 'insert',
      'method' => 'replaceWith',
      'selector' => $this->selector,
      'data' => \Drupal::service('renderer')->renderRoot($build),
      'settings' => $this->settings,
    ];
  }

}
