<?php

namespace App\Repositories;

use App\Models\Aplikasi;

class AplikasiRepository extends BaseRepository
{
    public function __construct(Aplikasi $model)
    {
        parent::__construct($model);
    }
}
