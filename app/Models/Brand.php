<?php

namespace App\Models;

use App\Core\Database;

class Brand
{
    public static function active(): array
    {
        return Database::getConnection()
            ->query('SELECT name, logo_path FROM brands WHERE is_active = 1 ORDER BY name')
            ->fetchAll();
    }
}
