<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Jobs\ItemCSVUploadJob;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\SheduleItem;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use Session;

class ProductController extends Controller
{
    public $global_language;

    public function __construct()
    {
        $this->middleware('auth');

        if (Session::has('language')) {
            $data = DB::table('languages')->find(Session::get('language'));
            $data_results = file_get_contents('assets/languages/' . $data->file);
            $this->vendor_language = json_decode($data_results);
        } else {
            $data = DB::table('languages')->where('is_default', '=', 1)->first();
            $data_results = file_get_contents('assets/languages/' . $data->file);
            $this->vendor_language = json_decode($data_results);
        }
    }





    //*** GET Request
    public function status($id1, $id2)
    {
        $data = Product::findOrFail($id1);
        $data->status = $id2;
        $data->update();
    }


    //*** POST Request
    public function import()
    {
        $cats = Category::all();
        $sign = Currency::where('is_default', '=', 1)->first();
        return view('vendor.product.productcsv', compact('cats', 'sign'));
    }


    public function store(Request $request)
    {

        if ($request->type == 'link') {
            $request->validate([
                'link' => 'required|url',
            ]);
            // Assuming $request->link contains a valid URL
            $path = file_get_contents($request->link);
            $data = array_map('str_getcsv', explode("\n", $path));
        } else {
            $request->validate([
                'csvfile' => 'required|mimes:csv',
            ]);
            // Assuming you are using the correct field name 'file' in your form
            $path = $request->file('csvfile')->getRealPath();
            $data = array_map('str_getcsv', file($path));
        }

        $csv_data = array_slice($data, 0, 2);
        $ttt = '';
        $tcheck = '';
        foreach ($csv_data[0] as $da) {
            $ttt .= "'$da'" . ',';
            $tcheck .= "$da" . ',';
        }

        Config::set('app.db_fields', explode(',', $tcheck));

        $type = $request->type;
        $link = $request->link;

        if (count($data) > 0) {
            $csv_header_fields = [];
            foreach ($data[0] as $key => $value) {
                $csv_header_fields[] = $key;
            }

            $csv_data = array_slice($data, 0, 2);
            $file_name = Str::random(8);

            if ($request->type == 'link') {
                file_put_contents('assets/temp/' . $file_name . '.csv', $path);
            } else {
                $request->csvfile->move('assets/temp', $file_name . '.csv');
            }

            return view('vendor.product.check', compact('csv_header_fields', 'csv_data', 'file_name', 'type', 'link'));
        } else {
            return redirect()->back();
        }
    }



    public function importSubmit(Request $request)
    {


        $request->validate([
            'category_id' => 'required',
            'sku' => 'required',
            'affiliate_link' => 'required',
            'product_name' => 'required',
            'current_price' => 'required',
            'product_description' => 'required',
        ]);


        if ($request->has('check_shedule') && $request->check_shedule == 1) {
            $request->validate([
                'shedule_date' => 'required',
            ]);

            $date = Carbon::parse($request->shedule_date)->format('Y-m-d H:i:s');
            $sheduleItem = new SheduleItem();
            $sheduleItem->shedule_date = $date;
            $sheduleItem->repeat_type = $request->shedule_repeat;
            $sheduleItem->file_name = $request->file_name . '.csv';
            $sheduleItem->type = $request->type;
            $sheduleItem->link = $request->link;
            $sheduleItem->request_data = json_encode($request->all());
            $sheduleItem->created_at =  Carbon::now();
            $sheduleItem->vendor_id = Auth::user()->id;
            $sheduleItem->save();

            return redirect()->route('vendor-prod-import')->with('success', 'Shedule Created Successfully started at ' . $date);
        }


        $csv = array_map('str_getcsv', file('assets/temp/' . $request->file_name . '.csv'));

        $chunks = array_chunk($csv, 100);
        $header = [];
        foreach ($chunks as $key => $chunk) {
            $data = $chunk;
            if ($key == 0) {
                $header = $data[0];
                unset($data[0]);
            }
            ItemCSVUploadJob::dispatch($header, $data, json_encode($request->all()), auth()->user()->id);
        }

        return redirect()->route('vendor-prod-import')->with('success', 'Product is queued for import, it will take some time to import');
    }






    //*** GET Request
    public function edit($id)
    {
        $cats = Category::all();
        $data = Product::findOrFail($id);
        $sign = Currency::where('is_default', '=', 1)->first();


        if ($data->type == 'Digital')
            return view('vendor.product.edit.digital', compact('cats', 'data', 'sign'));
        elseif ($data->type == 'License')
            return view('vendor.product.edit.license', compact('cats', 'data', 'sign'));
        else
            return view('vendor.product.edit.physical', compact('cats', 'data', 'sign'));
    }


    //*** GET Request CATALOG
    public function catalogedit($id)
    {
        $cats = Category::all();
        $data = Product::findOrFail($id);
        $sign = Currency::where('is_default', '=', 1)->first();


        if ($data->type == 'Digital')
            return view('vendor.product.edit.catalog.digital', compact('cats', 'data', 'sign'));
        elseif ($data->type == 'License')
            return view('vendor.product.edit.catalog.license', compact('cats', 'data', 'sign'));
        else
            return view('vendor.product.edit.catalog.physical', compact('cats', 'data', 'sign'));
    }




    //*** GET Request
    public function destroy($id)
    {

        $data = Product::findOrFail($id);
        if ($data->galleries->count() > 0) {
            foreach ($data->galleries as $gal) {
                if (file_exists('assets/images/galleries/' . $gal->photo)) {
                    unlink('assets/images/galleries/' . $gal->photo);
                }
                $gal->delete();
            }
        }

        if ($data->ratings->count() > 0) {
            foreach ($data->ratings  as $gal) {
                $gal->delete();
            }
        }
        if ($data->wishlists->count() > 0) {
            foreach ($data->wishlists as $gal) {
                $gal->delete();
            }
        }
        if ($data->clicks->count() > 0) {
            foreach ($data->clicks as $gal) {
                $gal->delete();
            }
        }
        if ($data->comments->count() > 0) {
            foreach ($data->comments as $gal) {
                if ($gal->replies->count() > 0) {
                    foreach ($gal->replies as $key) {
                        $key->delete();
                    }
                }
                $gal->delete();
            }
        }

        if (!filter_var($data->photo, FILTER_VALIDATE_URL)) {
            if (file_exists('assets/images/products/' . $data->photo)) {
                unlink('assets/images/products/' . $data->photo);
            }
        }

        if (file_exists('assets/images/thumbnails/' . $data->thumbnail) && $data->thumbnail != "") {
            unlink('assets/images/thumbnails/' . $data->thumbnail);
        }
        if ($data->file != null) {
            if (file_exists('assets/files/' . $data->file)) {
                unlink('assets/files/' . $data->file);
            }
        }
        $data->delete();
        //--- Redirect Section
        $msg = 'Product Deleted Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

        // PRODUCT DELETE ENDS
    }

    public function getAttributes(Request $request)
    {
        $model = '';
        if ($request->type == 'category') {
            $model = 'App\Models\Category';
        } elseif ($request->type == 'subcategory') {
            $model = 'App\Models\Subcategory';
        } elseif ($request->type == 'childcategory') {
            $model = 'App\Models\Childcategory';
        }

        $attributes = Attribute::where('attributable_id', $request->id)->where('attributable_type', $model)->get();
        $attrOptions = [];
        foreach ($attributes as $key => $attribute) {
            $options = AttributeOption::where('attribute_id', $attribute->id)->get();
            $attrOptions[] = ['attribute' => $attribute, 'options' => $options];
        }
        return response()->json($attrOptions);
    }
}
