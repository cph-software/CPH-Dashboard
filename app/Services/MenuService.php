<?php

namespace App\Services;

use App\Repositories\MenuRepository;

class MenuService extends BaseService
{
    public function __construct(MenuRepository $repository)
    {
        parent::__construct($repository);
    }
}
