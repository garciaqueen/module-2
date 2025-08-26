<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;


class GuestBookForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return'guest_book_form';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name_message'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'name-message'],
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Name'),
      '#required' => TRUE,
      '#description' => $this->t('The name length should be between 2-100 characters.'),
      '#ajax' => [
        'callback' => '::validateName',
        'event' => 'blur',
        'wrapper' => 'name-message',
      ],
    ];
    $form['email_message'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'email-message'],
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your Email'),
      '#required' => TRUE,
      '#description' => $this->t('Use standart format. Example: info@gmail.com'),
      '#ajax' => [
        'callback' => '::validateEmail',
        'event' => 'blur',
        'wrapper' => 'email-message',
      ],
    ];
    $form['phone_message'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'phone-message'],
    ];
    $form['phone_number'] = [
      '#type' => 'number',
      '#title' => $this->t('Your Phone Number'),
      '#required' => TRUE,
      '#description' => $this->t('Example format: 0687073454'),
      '#ajax' => [
        'callback' => '::validateNumber',
        'event' => 'blur',
        'wrapper' => 'phone-message',
      ],
    ];
    

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function validateName(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $name = trim($form_state->getValue('name'));

    if (!$name) {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--warning">' . $this->t('Enter your name!') . '</div>'));
    } elseif (strlen($name) < 2) {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--error">' . $this->t('Too short!') . '</div>'));
    } elseif (strlen($name) > 100) {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--error">' . $this->t('Too long!') . '</div>'));
    } else {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--success">' . $this->t('Correct!') . '</div>'));
    }

    return $response;
  }

  public function validateEmail(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $email = trim($form_state->getValue('email'));

    if (!$email) {
      $response->addCommand(new HtmlCommand('#email-message', '<div class="messages messages--warning">' . $this->t('Enter your email!') . '</div>'));
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
      $response->addCommand(new HtmlCommand('#email-message', '<div class="messages messages--error">' . $this->t('The email format is incorrect!') . '</div>'));
    }  else {
      $response->addCommand(new HtmlCommand('#email-message', '<div class="messages messages--success">' . $this->t('Correct!') . '</div>'));
    }
    

    return $response;

  }

  public function validateNumber(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $phone_number = trim($form_state->getValue('phone_number'));

    if (!$phone_number) {
      $response->addCommand(new HtmlCommand('#phone-message', '<div class="messages messages--warning">' . $this->t('Enter your phone number!') . '</div>'));
    } elseif (strlen($phone_number) != 10) {
      $response->addCommand(new HtmlCommand('#phone-message', '<div class="messages messages--error">' . $this->t('The phone number format is incorrect!') . '</div>'));
    }  else {
      $response->addCommand(new HtmlCommand('#phone-message', '<div class="messages messages--success">' . $this->t('Correct!') . '</div>'));
    }

    return $response;

  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::database()->insert('guest_book')
      ->fields([
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'phone_number' => $form_state->getValue('phone_number'),
        'created' => \Drupal::time()->getCurrentTime(),
      ])
      ->execute();


  }
}