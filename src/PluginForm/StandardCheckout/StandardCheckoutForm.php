<?php

namespace Drupal\commerce_epayco\PluginForm\StandardCheckout;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;

class StandardCheckoutForm extends BasePaymentOffsiteForm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = $this->entity;
    /** @var \Drupal\commerce_epayco\Plugin\Commerce\PaymentGateway\StandardCheckoutInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();

    $redirect_url = "https://secure.payco.co/checkout.php";

    $data = [
      'p_cust_id_cliente' => $payment_gateway_plugin->getCommerceID(),
      'p_key' => $payment_gateway_plugin->getCommerceKey(),
      'p_id_invoice' => $payment->getOrderID(),
      'p_description' => 'Test', // TODO get or generate an order description
      'p_amount' => number_format($payment->getAmount()->getNumber(),0,'.',''),
      'p_amount_base' => number_format($payment->getAmount()->getNumber(),0,'.',''),
      'p_tax' => 0, // TODO get tax
      'p_email' => 'info@alasdemariposa.com', // TODO get client email
      'p_currency_code' => "COP", // TODO get currency code
      'p_signature' => $this->getSignature($payment, $payment_gateway_plugin), // TODO see note in the function
      'p_test_request' => 'TRUE', // TODO configuration to stablish if is test or production
      'p_url_response' => $form['#return_url'],
    ];

    return $this->buildRedirectForm($form, $form_state, $redirect_url, $data, 'post');
  }

  // TODO not sure if this function should be here or in StandardCheckoutInterface to be
  // implemented on StandardChekout class
  public function getSignature($payment, $payment_gateway_plugin){
    $signatureInfo = [
      'p_cust_id_cliente' => $payment_gateway_plugin->getCommerceID(),
      'p_key' => $payment_gateway_plugin->getCommerceKey(),
      'p_id_invoice' => $payment->getOrderID(),
      'p_amount' => number_format($payment->getAmount()->getNumber(),0,'.',''),
      'p_currency_code' => "COP",
    ];
    return md5(implode("^", $signatureInfo));
  }
}
