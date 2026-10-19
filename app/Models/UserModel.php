<?php

namespace App\Models;

use CodeIgniter\Model;

final class UserModel extends Model
{
    protected $table = "users";
    protected $primaryKey = "id";
    protected $returnType = "array";
    protected $allowedFields = [
        "nama",
        "username",
        "password_hash",
        "role",
        "aktif",
        "auth_version",
        "created_at",
        "updated_at",
    ];
}
