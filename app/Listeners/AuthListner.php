<?php

namespace App\Listeners;

use App\Events\AuthEvent;
use App\Models\Bookmark;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthListner
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AuthEvent $event): void
    {
        $userId = $event->userId;

        if (blank($userId)) {
            return;
        } else if (Session::has('user.bookmarks')) {
            $bookmarks = Session::pull('user.bookmarks');

            foreach ($bookmarks as $key => $bookmark) {
                $bookmarkToDb = Bookmark::create([
                    'user_id' => $userId,
                    'book_id' => $key,
                    'quantity' => $bookmark['quantity'],
                    // 'price'=>
                    // 'title'=>
                    // 'author'=>
                ]);
            }
        }
    }
}
