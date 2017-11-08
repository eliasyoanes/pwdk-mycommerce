<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    
    $category_list = DB::select('select categoryName, categoryImage from categories');
    // ini masukin array ke kategori list dari database
    
    // for($i = 1; $i <= 10; $i++){
    //     $obj = new stdClass;
    //     $obj->category_name = "Category " . $i;

    //     array_push($category_list, $obj);
    // }

    return view('home', ["categories" => $category_list]);
    // ini masukin array yang ada di variabel category_list ke categories
});

Route::group(['prefix' => 'products'], function() {
    Route::get('/all', function () {
        return view('product.product-many');
    });
    Route::get('/detail/{id?}', function ($id) {

        
        // $product = DB::select('select * from products where $id = ?', [$id]->first());

        $product = DB::table('products')
                    ->where('id', '=', $id)
                    ->first();
        // -> first buat return ga di dalam array of object

        $productDetails = DB::table('product_details')
                            ->where('productId', '=', $id)
                            ->get();
        
        $productImages = DB::table('product_images')
                        ->where('productId', '=', $id)
                        ->get();

        return view('product.product-single', ["p" => $product, "pd" => $productDetails, "pi" => $productImages]);
        // kl mau querynya bisa di simpen di dalem controller 
    });
    Route::get('/bycategory', function () {
        return view('product.product-many');
    });
});

Route::group(['prefix' => 'orders'], function() {
    Route::post('/addtocart', function (Request $request) {
        //handler untuk method post dari blade yang add to cart
        $postcart_product_id = Illuminate\Support\Facades\Request::input('product_id');        
        $postcart_product_details_id = Illuminate\Support\Facades\Request::input('product_details_id');
        
        // ini insert pake query biasa
        // DB::insert("
        //             insert into orders (code, orderDate, deliveryOption, total)
        //             values
        //             ('". uniqid() ."', NOW(), '', 0) 
        // ");
        // uniqid adalah untuk random string

        //insert pake query builder
        $order = DB::table('orders')
                ->where('status','=',0)
                ->first();

        if(empty($order)){
            $orderId = DB::table('orders')->insertGetId(
                [
                    "code" => uniqid(),
                    "orderDate" => new \DateTime(),
                    "deliveryOption" => "",
                    "total" => 0
                ]
            );
        } else{
            $orderId = $order->id;
        }
        // pake emty itu buat cek di database ada ga yang statusnya 0,kl null (ga ada atau 1 semua) baru buat order baru


        $product = DB::table('products')
                        ->where('id','=', $postcart_product_id )
                        ->first();
        // pake insert get id supaya bs lgsg return idnya
        // product id dari line 63 yang postcart

        $orderDetails = DB::table('order_details')->insert(
            [
                "orderId" => $orderId,
                "productDetailsId" => $postcart_product_details_id,
                "qty" => 1,
                "price" => $product->price
            ]
        );


        return $postcart_product_details_id;
        

    });
    Route::get('/addtocart', function () {
        return view('order.cart');
    });

    Route::get('/checkout', function () {
        return view('order.checkout');
    });

    Route::post('/checkout', function () {
        return view('order.checkout');
    });
});

Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
