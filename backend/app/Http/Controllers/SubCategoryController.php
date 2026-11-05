<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubCategoryRequest;
use App\Models\SubCategory;
use App\Services\SubCategoryService;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function __construct(private SubCategoryService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->service->paginate($request->query('search'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubCategoryRequest $request)
    {
        return response()->json($this->service->create($request->validated()), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubCategoryRequest $request, SubCategory $subCategory)
    {
        return $this->service->update($subCategory, $request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        $this->service->delete($subCategory);

        return response()->noContent();
    }
}
