<?php

namespace Drupal\commerce_epayco\Plugin\Commerce\PaymentGateway;

interface StandardCheckoutInterface {
  /**
   * Gets commerce ID.
   *
   * @return string
   *   The commerce ID.
   */
   public function getCommerceID();

   /**
   * Gets commerce secret key.
   *
   * @return string
   *   The commerce secret key.
   */
   public function getCommerceKey();

   /**
   * Gets Commerce Test Mode.
   *
   * @return string
   *   TRUE for test request.
   */
  public function isTestRequest();
}
