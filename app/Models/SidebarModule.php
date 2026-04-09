<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidebarModule extends Model
{

    protected $fillable = [
        'title',
        'icon',
        'permission',
        'order',
    ];
    public function options()
    {
        return $this->hasMany(SidebarOption::class, 'sidebar_module_id')->orderBy('order');
    }

}
