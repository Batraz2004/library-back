<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(Admin $admin)
    {
        return $admin->can('viewAny Post');
    }

    public function view(Admin $admin, Post $post)
    {
        return $admin->can('view Post');
    }

    public function create(Admin $admin)
    {
        return $admin->can('create Post');
    }

    public function update(Admin $admin, Post $post)
    {
        return $admin->can('update Post') && $admin->id == $post->admin_id;
    }

    public function delete(Admin $admin)
    {
        $admin->can('delete Post');
    }
}
