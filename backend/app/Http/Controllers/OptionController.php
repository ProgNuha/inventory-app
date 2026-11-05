<?php

namespace App\Http\Controllers;

use App\Services\OptionService;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function __construct(private OptionService $service) {}

    public function categories()
    {
        return $this->service->categories();
    }

    public function subCategories(Request $request)
    {
        return $this->service->subCategories($request->integer('category_id') ?: null);
    }

    public function operators()
    {
        return $this->service->operators();
    }
}