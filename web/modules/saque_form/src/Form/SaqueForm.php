<?php

namespace Drupal\saque_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a saque_form form.
 */
class SaqueForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'saque_form_saque';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['valor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor do Saque'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Efetuar Saque'),
    ];

    if ($form_state->isRebuilding()) {

      $output_valor = number_format($form_state->getValue('valor'), 2, ',', '.');
      $distribuicao_cedulas = $form_state->get('distribuicao_cedulas');

      $form['valor_sacado'] = [
        '#type' => 'markup',
        '#markup' => $this->t("<b>Valor de Saque:</b> R$ ".$output_valor."<br>"),
      ];

      $form['total_cedulas'] = [
        '#type' => 'markup',
        '#markup' => $this->t("<b>Quantidade de Cédulas:</b> ".$form_state->get('total_cedulas')."<br>"),
      ];

      $form['distribuicao_cedulas'] = [
        '#type' => 'markup',
        '#markup' => $this->t("<b>Distribuição de Cédulas:</b><br>".implode($distribuicao_cedulas)),
      ];

    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $valor = $form_state->getValue('valor');

    if(!is_numeric($valor) || $valor != round($valor) || $valor <= 0){
      $form_state->setErrorByName('valor', $this->t('Por favor, insira um valor inteiro válido.'));
      unset($form['valor_sacado']);
      unset($form['total_cedulas']);
      unset($form['distribuicao_cedulas']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $valor = $form_state->getValue('valor');
    $cedulas = [100, 50, 10, 5, 2, 1];
    $total_cedulas = 0;
    $distribuicao_cedulas = [];

    for ($i = 0; $i < count($cedulas); $i++) {
      $qtde_cedulas = 0;

      if($valor >= $cedulas[$i]) {
        $qtde_cedulas = floor($valor / $cedulas[$i]);
        $distribuicao_cedulas[$i] = "$qtde_cedulas cédula(s) de R$ $cedulas[$i]<br>";

        $total_cedulas += $qtde_cedulas;
        $valor = $valor % $cedulas[$i];
      }
    }

    $form_state->set('total_cedulas', $total_cedulas);
    $form_state->set('distribuicao_cedulas', $distribuicao_cedulas);

    $this->messenger()->addStatus($this->t('Saque efetuado com sucesso!'));
    $form_state->setRebuild();
  }

}
