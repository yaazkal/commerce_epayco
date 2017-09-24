<?php
namespace Drupal\commerce_epayco\Plugin\Commerce\PaymentGateway;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\commerce_epayco\Plugin\Commerce\PaymentGateway\StandardCheckoutInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Exception\PaymentGatewayException;

/**
 * Provides the StandardCheckout payment gateway
 * @CommercePaymentGateway(
 *   id = "epayco_standard",
 *   label = "ePayco (Standard Checkout)",
 *   display_label = "ePayco",
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_epayco\PluginForm\StandardCheckout\StandardCheckoutForm"
 *   }
 * )
 */
class StandardCheckout extends OffsitePaymentGatewayBase implements StandardCheckoutInterface {
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'p_cust_id_cliente' => '',
      'p_key' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['p_cust_id_cliente'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $this->configuration['p_cust_id_cliente'],
      '#required' => TRUE,
    ];
    $form['p_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret key'),
      '#default_value' => $this->configuration['p_key'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['p_cust_id_cliente'] = $values['p_cust_id_cliente'];
      $this->configuration['p_key'] = $values['p_key'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onReturn(OrderInterface $order, Request $request) {
    if ($request->query->get('x_cod_response') == '2' || $request->query->get('x_cod_response') == '4') {
      throw new PaymentGatewayException($request->query->get('x_response_reason_text'), $request->query->get('x_response'));
    }
    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    $payment = $payment_storage->create([
      'state' => 'authorization',
      'amount' => $order->getTotalPrice(),
      'payment_gateway' => $this->entityId,
      'order_id' => $order->id(),
      'remote_id' => $request->query->get('x_transaction_id'),
      'remote_state' => $request->query->get('x_cod_response'),
    ]);

    if ($request->query->get('x_cod_response') == '1') {
      $payment->state = 'completed';
    }

    $payment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getCommerceID() {
    return $this->configuration['p_cust_id_cliente'];
  }

  /**
   * {@inheritdoc}
   */
   public function getCommerceKey() {
    return $this->configuration['p_key'];
  }
}
