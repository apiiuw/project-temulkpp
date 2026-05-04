<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'route', 'icon', 'category'];

    public function roles()
    {
        return $this->hasMany(RoleMenu::class);
    }
}


