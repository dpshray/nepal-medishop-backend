<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\CategoriesRequest;
use App\Models\Categories;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class CategoriesController extends Controller
{
    //
    use ResponseTrait;
    public function add_categories(CategoriesRequest $request)
    {
        $slug = Str::slug($request->title);
        // Create Category
        $category = Categories::create([
            'title' => $request->title,
            'slug' => $slug,
        ]);

        if (!$category) {
            return $this->apiError("Failed to add category");
        }
        return $this->apiSuccess("Category added successfully",);
    }
     public function update_categories(CategoriesRequest $request, Categories $categories)
    {
        $updated = $categories->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
        ]);

        if (!$updated) {
            return $this->apiError('Failed to update');
        }
        return $this->apiSuccess('Category updated successfully');
    }
    public function delete_categories(Categories $category)
    {
        $deleted = $category->delete();

        if (!$deleted) {
            return $this->apiError('Failed to delete category');
        }

        return $this->apiSuccess('Category deleted successfully');
    }
}
