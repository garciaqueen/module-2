<?php

namespace Drupal\guest_book\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileSystemInterface;


class GuestBookForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'guest_book_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="mashka-form-wrapper">';
    $form['#suffix'] = '</div>';
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
      '#description' => $this->t('Use standard format. Example: info@gmail.com'),
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

    $form['comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your Comment'),
      '#required' => TRUE,
    ];

    $form['avatar_message'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'avatar-message'],
    ];
    $form['avatar'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Avatar'),
      '#description' => $this->t('Allowed formats: jpeg, jpg, png. Max size: 2 MB'),
      '#upload_location' => 'public://guestbook/avatars/',
      '#required' => FALSE,
      '#multiple' => FALSE,
      '#upload_validators' => [
        'FileExtension' => [
          'extensions' => 'jpg jpeg png',
        ],
        'FileSizeLimit' => [
          'fileLimit' => 2097152,
        ],
      ],
      '#ajax' => [
        'callback' => '::validateAvatar',
        'event' => 'change',
        'wrapper' => 'avatar-message',
      ],
    ];

    $form['image'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'image-message'],
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Image'),
      '#description' => $this->t('Allowed formats: jpeg, jpg, png. Max size: 5 MB'),
      '#upload_location' => 'public://guestbook/images/',
      '#required' => FALSE,
      '#multiple' => FALSE,
      '#upload_validators' => [
        'FileExtension' => [
          'extensions' => 'jpg jpeg png',
        ],
        'FileSizeLimit' => [
          'fileLimit' => 5242880,
        ],
      ],
      '#ajax' => [
        'callback' => '::validateImage',
        'event' => 'change',
        'wrapper' => 'image-message',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Simple AJAX name validation.
   */
  public function validateName(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $name = trim($form_state->getValue('name'));

    if (!$name) {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--warning">Enter your name!</div>'));
    } elseif (strlen($name) < 2) {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--error">Too short!</div>'));
    } elseif (strlen($name) > 100) {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--error">Too long!</div>'));
    } else {
      $response->addCommand(new HtmlCommand('#name-message', '<div class="messages messages--success">Correct!</div>'));
    }
    return $response;
  }

  public function validateEmail(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $email = trim($form_state->getValue('email'));

    if (!$email) {
      $response->addCommand(new HtmlCommand('#email-message', '<div class="messages messages--warning">Enter your email!</div>'));
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
      $response->addCommand(new HtmlCommand('#email-message', '<div class="messages messages--error">Invalid email format!</div>'));
    } else {
      $response->addCommand(new HtmlCommand('#email-message', '<div class="messages messages--success">Correct!</div>'));
    }
    return $response;
  }

  public function validateNumber(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $phone_number = trim($form_state->getValue('phone_number'));

    if (!$phone_number) {
      $response->addCommand(new HtmlCommand('#phone-message', '<div class="messages messages--warning">Enter your phone number!</div>'));
    } elseif (strlen($phone_number) != 10) {
      $response->addCommand(new HtmlCommand('#phone-message', '<div class="messages messages--error">Phone number must be 10 digits.</div>'));
    } else {
      $response->addCommand(new HtmlCommand('#phone-message', '<div class="messages messages--success">Correct!</div>'));
    }
    return $response;
  }

  public function validateAvatar(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $errors = $form_state->getErrors();
    if (!empty($errors)) {
      $error_messages = [];
      foreach ($errors as $error) {
        $error_messages[] = $error;
      }
      $response->addCommand(new HtmlCommand('#avatar-message', '<div class="messages messages--error">' . implode('<br>', $error_messages) . '</div>'));
      return $response;
    }

    $fid = $form_state->getValue('avatar')[0] ?? NULL;

    if ($fid) {
      $file = File::load($fid);
      if ($file) {
        $response->addCommand(new HtmlCommand('#avatar-message', '<div class="messages messages--success">✅ Avatar file looks good!</div>'));
      }
    } else {
      $response->addCommand(new HtmlCommand('#avatar-message', ''));
    }

    return $response;
  }
    public function validateImage(array &$form, FormStateInterface $form_state) {

      $response = new AjaxResponse();

      $errors = $form_state->getErrors();
      if (!empty($errors)) {
        $error_messages = [];
        foreach ($errors as $error) {
          $error_messages[] = $error;
        }
        $response->addCommand(new HtmlCommand('#image-message', '<div class="messages messages--error">' . implode('<br>', $error_messages) . '</div>'));
        return $response;
      }

      $fid = $form_state->getValue('image')[0] ?? NULL;

      if ($fid) {
        $file = File::load($fid);
        if ($file) {
          $response->addCommand(new HtmlCommand('#image-message', '<div class="messages messages--success">✅ Image file looks good!</div>'));
        }
      } else {
        $response->addCommand(new HtmlCommand('#image-message', ''));
      }
      return $response;
    }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    
    $avatar_fid = $form_state->getValue('avatar')[0] ?? NULL;
    $image_fid = $form_state->getValue('image')[0] ?? NULL;
    
    if ($avatar_fid) {
      $file = File::load($avatar_fid);
      if ($file) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
          $form_state->setErrorByName('avatar', $this->t('Only JPG, JPEG, and PNG files are allowed.'));
        }
        
        if ($file->getSize() > 2097152) {
          $form_state->setErrorByName('avatar', $this->t('File size must be less than 2MB.'));
        }
        
        $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowed_mimes)) {
          $form_state->setErrorByName('avatar', $this->t('Invalid file type. Only JPG and PNG images are allowed.'));
        }
      }
    }

    if ($image_fid) {
      $file = File::load($image_fid);
      if ($file) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions)) {
          $form_state->setErrorByName('image', $this->t('Only JPG, JPEG, and PNG files are allowed.'));
        }
        
        if ($file->getSize() > 2097152) {
          $form_state->setErrorByName('image', $this->t('File size must be less than 5MB.'));
        }
        
        $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowed_mimes)) {
          $form_state->setErrorByName('image', $this->t('Invalid file type. Only JPG and PNG images are allowed.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $avatar_fid = $form_state->getValue('avatar')[0] ?? NULL;
    $image_fid = $form_state->getValue('image')[0] ?? NULL;

    $new_filename = NULL;
    $new_filename_img = NULL;


    if ($avatar_fid) {
      $file = File::load($avatar_fid);

      if ($file) {
        $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

        $uid = \Drupal::currentUser()->id();

        $new_filename = $uid . '-' . \Drupal::time()->getRequestTime() . '.' . $extension;

        $destination = 'public://guestbook/avatars/' . $new_filename;
        \Drupal::service('file_system')->move($file->getFileUri(), $destination, FileSystemInterface::EXISTS_REPLACE);

        $file->setFilename($new_filename);
        $file->setFileUri($destination);

        $file->setPermanent();
        $file->save();
      }
    }

    if ($image_fid) {
      $file = File::load($image_fid);

      if ($file) {
        $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

        $uid = \Drupal::currentUser()->id();

        $new_filename_img = $uid . '-' . \Drupal::time()->getRequestTime() . '.' . $extension;

        $destination = 'public://guestbook/images/' . $new_filename_img;
        \Drupal::service('file_system')->move($file->getFileUri(), $destination, FileSystemInterface::EXISTS_REPLACE);

        $file->setFilename($new_filename_img);
        $file->setFileUri($destination);

        $file->setPermanent();
        $file->save();
      }
    }

    \Drupal::database()->insert('guest_book')
      ->fields([
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'phone_number' => $form_state->getValue('phone_number'),
        'comment' => $form_state->getValue('comment'),
        'avatar' => $new_filename,
        'image' => $new_filename_img,
        'created' => \Drupal::time()->getRequestTime(),
      ])
      ->execute();

    $this->messenger()->addStatus($this->t('Comment added successfully!'));
  }


}