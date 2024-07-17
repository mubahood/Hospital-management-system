<?php

namespace App\Admin\Extensions\Nav;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;

class Dropdown implements Renderable
{
  public function render()
  {
 
    return view('widgets/dropdown', [
      'links' => [
        [
          'url' => admin_url('job-applications'),
          'icon' => 'wpforms',
          'title' => 'My job applications',
        ],
        [
          'url' => admin_url('product-orders'),
          'icon' => 'shopping-cart',
          'title' => 'My products orders',
        ],
        [
          'url' => admin_url('event-bookings'),
          'icon' => 'bookmark',
          'title' => 'My event bookings',
        ],
        [
          'url' => admin_url('auth/setting'),
          'icon' => 'edit',
          'title' => 'Update my profile',
        ],
      ]
    ]);
  }
}
