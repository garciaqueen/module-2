<?php

namespace Drupal\guest_book\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class GuestBookController extends ControllerBase {

  public function page() {
    return [
      '#theme' => 'guest_book_page',
      '#form' => $this->formBuilder()->getForm(\Drupal\guest_book\Form\GuestBookForm::class),
      '#reviews' => $this->reviewList(),
    ];
  }

  public function reviewList() {
    $reviews = \Drupal::database()->select('guest_book', 'g')
      ->fields('g', ['id', 'name', 'email', 'comment', 'avatar', 'image', 'created'])
      ->execute()
      ->fetchAll();

    $base_url = \Drupal::request()->getSchemeAndHttpHost();
    $items = [];
      foreach ($reviews as $review) {
        $items[] = [
          'id' => $review->id,
          'name' => $review->name,
          'email' => $review->email,
          'comment' => $review->comment,
          'avatar' => !empty($review->avatar)
            ? $base_url . '/sites/default/files/guestbook/avatars/' . $review->avatar
            : 'https://www.svgrepo.com/show/452030/avatar-default.svg',
          'image' => !empty($review->image)
          ? $base_url . '/sites/default/files/guestbook/images/' . $review->image
          : NULL,
          'created' => $review->created,
        ];
      }

    return $items;
  }

  public function confirmDelete($id) {
    return [
      '#type' => 'container',
      'text' => [
        '#markup' => '<p>Are you sure you want to delete this review?</p>',
      ],
      'yes' => [
        '#type' => 'link',
        '#title' => $this->t('Yes'),
        '#url' => Url::fromRoute('guest_book.delete', ['id' => $id]),
        '#attributes' => ['class' => ['btn', 'btn-danger', 'left']],
      ],
      'cancel' => [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#url' => Url::fromRoute('guest_book.page'),
        '#attributes' => [
          'class' => ['btn', 'btn-secondary', 'right'],
          'data-dialog-close' => 'true',
        ],
      ],
    ];
  }
  public function deleteReview($id) {
    \Drupal::database()->delete('guest_book')
      ->condition('id', $id)
      ->execute();

    $this->messenger()->addMessage($this->t('Review deleted.'));
    return $this->redirect('guest_book.page'); // redirect after deletion
  }

}

