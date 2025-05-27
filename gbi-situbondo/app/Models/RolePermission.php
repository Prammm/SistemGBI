<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;
    
    protected $table = 'role_permissions';
    protected $primaryKey = 'id_role_permission';
    
    protected $fillable = [
        'id_role', 'id_permission',
    ];
    
    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }
    
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'id_permission', 'id_permission');
    }
}