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
                    : NULL,
                    'image' => !empty($review->image)
                    ? $base_url . '/sites/default/files/guestbook/images/' . $review->image
                    : NULL,
                    'created' => $review->created,
                ];
                }

                return $items;
    }
}

