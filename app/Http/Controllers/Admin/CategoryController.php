<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Categories;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.products.create-categories');
    }

    public function store(Request $request)
    {
        $base = 'CAT-' . strtoupper(Str::substr(preg_replace('/[^A-Za-z]/', '', $request->name), 0, 3));
        $code = $base;
        $i = 1;
        while (Categories::where('code', $code)->exists()) {
            $code = $base . $i++;
        }
     
        if(Categories::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Category name already exists.'], 422);
        }

        $sort_order = Categories::max('sort_order') + 1;
        
        $category = Categories::create([
            'name' => $request->input('name'),
            'svg' => $request->input('svg'),
            'code' => $code,
            'sort_order' => $sort_order,
        ]);

        $category->save();

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.', 
            'category' => $category]);
    }

    public function update(int $id, Request $request)
    {
        $category = Categories::findOrFail($id);
       
        if(Categories::where('name', $request->name)->where('id', '!=', $id)->exists()) {
            return response()->json(['message' => 'Category name already exists.'], 422);
        }

        $category->update([
            'name' => $request->input('name'),
            'svg' => $request->input('svg'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'category' => $category
        ]);
    }
}
