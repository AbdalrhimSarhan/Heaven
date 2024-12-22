<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\StoreResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Psy\Readline\Hoa\_Protocol;

class CategoryController extends Controller
{
    public function index(Request $request) {

        $language = $request->get('lang', 'en');

        $categories = Category::all();

        $data = $categories->map(function($category) use ($language) {
            return [
                'id' => $category->id,
                'name' => $language === 'ar' ? $category->name_ar : $category->name_en,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });

        return ResponseHelper::jsonResponse($data, __('message.category.index'));
    }

    public function showStores(Request $request, Category $category)
    {
        $language = $request->get('lang', 'en');

        $stores = $category->stores;

        return ResponseHelper::jsonResponse([
            'category' => $language === 'ar' ? $category->name_ar : $category->name_en,
            'stores' => StoreResource::collection($stores)->additional(['lang' => $language]),
        ], __('message.category.show'));
    }

    public function createNewCategory(Request $request) {
        $validation = $request->validate([
            'name_en'=>'required|string|min:2|max:100',
            'name_ar'=>'required|string|min:2|max:100',
            'image' => 'required|image',
        ]);

        if ($request->hasFile('image')) {
            $validation['image'] = $request->file('image')->store('category_image');
        } else {
            $validation['image'] = null;
        }

        $category = Category::create([
            'name_en' => $validation['name_en'],
            'name_ar' => $validation['name_ar'],
            'image' => $validation['image'],
        ]);

        return ResponseHelper::jsonResponse($category, __('message.category.create'));
    }
}
