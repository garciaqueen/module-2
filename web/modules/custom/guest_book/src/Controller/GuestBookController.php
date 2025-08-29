<?php

namespace Drupal\guest_book\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller for Guest Book functionality.
 */
class GuestBookController extends ControllerBase {

  /**
   * Builds the guest book page with form and reviews list.
   *
   * @return array
   *   A render array for the guest book page.
   */
  public function page() {
    return [
      '#theme' => 'guest_book_page',
      '#intro' => 'Hello! You can add here a photo of your cat.',
      '#form' => $this->formBuilder()->getForm(\Drupal\guest_book\Form\GuestBookForm::class),
      '#reviews' => $this->reviewList(),
    ];
  }

  /**
   * Loads and returns a list of guest book reviews.
   *
   * @return array
   *   An array of reviews with id, name, email, comment, avatar, image, and created timestamp.
   */
  public function reviewList() {
    $reviews = \Drupal::database()->select('guest_book', 'g')
      ->fields('g', ['id', 'name', 'email', 'phone_number', 'comment', 'avatar', 'image', 'created'])
      ->orderBy('created', 'DESC')
      ->execute()
      ->fetchAll();

    $base_url = \Drupal::request()->getSchemeAndHttpHost();
    $items = [];

    foreach ($reviews as $review) {
      $items[] = [
        'id' => $review->id,
        'name' => $review->name,
        'email' => $review->email,
        'phone_number' => $review->phone_number,
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

  /**
   * Provides a confirmation render array for deleting a review.
   *
   * @param int $id
   *   The ID of the review to delete.
   *
   * @return array
   *   A render array for the confirmation UI.
   */
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
    ];
  }

  /**
   * Deletes a review from the database and redirects back to the guest book page.
   *
   * @param int $id
   *   The ID of the review to delete.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the guest book page.
   */
  public function deleteReview($id) {
    \Drupal::database()->delete('guest_book')
      ->condition('id', $id)
      ->execute();

    $this->messenger()->addMessage($this->t('Review deleted.'));
    return $this->redirect('guest_book.page');
  }

}
