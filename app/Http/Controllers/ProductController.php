<?php

namespace App\Http\Controllers;
use Modules\GlobalSetting\app\Models\Placeholders;
use Modules\Categories\app\Models\Categories;
use Modules\Product\app\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.dashboard'); // Return the view for the dashboard
    }

    public function productlist(Request $request) {
        $products = Product::query()->where('source_type','=','product')->get();

        return view('products',compact('products'));
    }
    
    public function productdetail(Request $request) {
        $products = Product::query()->where('slug','=',$request->slug)->first();
        return view('productdetail',compact('products'));
    }

    public function add(Request $request){
        $getplaceholder=Placeholders::select('placeholder_name','id')->where('status',1)->where('deleted_at',null)->get();
        return view('admin.invoice-template', compact('getplaceholder'));
    }

    public function showFormCategories(Request $request)
    {
        $categoryId = $request->session()->get('category_id');

        $categoryName = Categories::where('id', $categoryId)->value('name');

        return view('admin.form-categories', compact('categoryId', 'categoryName'));
    }
}
