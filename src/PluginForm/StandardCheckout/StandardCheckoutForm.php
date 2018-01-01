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

    $order = $payment->getOrder();

    $redirect_url = "https://secure.payco.co/checkout.php";

    $tax_total = 0;
    foreach ($order->collectAdjustments() as $key => $tax) {
      $tax_total += $tax->getAmount()->getNumber();
    }

    $amount = $payment->getAmount()->getNumber();

    $data = [
      'p_cust_id_cliente' => $payment_gateway_plugin->getCommerceID(),
      'p_key' => $payment_gateway_plugin->getCommerceKey(),
      'p_id_invoice' => $payment->getOrderID(),
      'p_description' => t('Payment for order No. @order_id', ['@order_id' => $payment->getOrderId()]),
      'p_amount' => number_format($amount,0,'.',''),
      'p_amount_base' => number_format(($amount-$tax_total),0,'.',''),
      'p_tax' => number_format($tax_total,0,'.',''),
      'p_email' => $order->getEmail(), // TODO get client email
      'p_currency_code' => $payment->getAmount()->getCurrencyCode(),
      'p_signature' => $this->getSignature($payment, $payment_gateway_plugin), // TODO see note in the function
      'p_test_request' => $payment_gateway_plugin->isTestRequest(),
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
      'p_currency_code' => $payment->getAmount()->getCurrencyCode(),
    ];
    return md5(implode("^", $signatureInfo));
  }
}
