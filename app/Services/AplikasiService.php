<?php

namespace App\Services;

use App\Repositories\AplikasiRepository;

class AplikasiService extends BaseService
{
    public function __construct(AplikasiRepository $repository)
    {
        parent::__construct($repository);
    }
}
