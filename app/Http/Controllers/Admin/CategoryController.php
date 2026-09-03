<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.products.create-categories');
    }

    public function store(Request $request)
    {
        $category = Categories::create([
            'name' => $request->input('name'),
            'svg' => $request->input('svg'),
        ]);

        $category->save();

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(int $id, Request $request)
    {
        $category = Categories::findOrFail($id);

        $category->name = $request->input('name');
        $category->svg = $request->input('svg');
        $category->save();

        return redirect()->back()->with('success', 'Category updated successfully.');
    }
}
