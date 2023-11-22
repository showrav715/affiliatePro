<?php

namespace App\Http\Controllers\Admin;

use App\Models\Childcategory;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Attribute;
use App\Models\AttributeOption;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ItemCSVUploadJob;
use App\Models\CsvType;
use App\Models\SheduleItem;
use Carbon\Carbon;
use Validator;

use DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Image;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
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
        return view('admin.product.productcsv', compact('cats', 'sign'));
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

            return view('admin.product.check', compact('csv_header_fields', 'csv_data', 'file_name','type','link'));
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
            $sheduleItem->save();

            return redirect()->route('admin-prod-import')->with('success', 'Shedule Created Successfully started at ' . $date);
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
            ItemCSVUploadJob::dispatch($header, $data, json_encode($request->all()));
        }

        return redirect()->route('admin-prod-import')->with('success', 'Product is queued for import, it will take some time to import');
    }


    public function process($header, $data, $request)
    {
        $real_datas = [];
        foreach ($data as $key => $check) {
            $new_array = array_combine($header, $check);
            $real_datas['category_id']      = $request->category_id;
            $real_datas['subcategory_id']   = $request->subcategory_id;
            $real_datas['childcategory_id'] = $request->childcategory_id;
            $real_datas['sku']              = $new_array[$request->sku];
            $real_datas['affiliate_link']   = $request->affiliate_link ? $new_array[$request->affiliate_link] : null;
            $real_datas['name']             = $new_array[$request->product_name];
            $real_datas['slug']             = Str::slug($new_array[$request->product_name] . $new_array[$request->sku]);
            $real_datas['size']             = $request->sizes ? $new_array[$request->sizes] : null;
            $real_datas['size_qty']         = $request->size_quantity ? $new_array[$request->size_quantity] : null;
            $real_datas['size_price']       = $request->size_extra_price ? $new_array[$request->size_extra_price] : null;
            $real_datas['color']            = $request->colors ? $new_array[$request->colors] : null;
            $real_datas['price']            = $request->current_price ? (is_numeric($new_array[$request->current_price]) ? $new_array[$request->current_price] : 0) : null;
            $real_datas['previous_price']   = $request->previous_price ? (is_numeric($new_array[$request->previous_price]) ? $new_array[$request->previous_price] : 0) : null;
            $real_datas['details']          = $request->product_description ? utf8_encode($new_array[$request->product_description]) : null;
            $real_datas['stock']            = $request->stock ?  (is_numeric($new_array[$request->stock]) ? $new_array[$request->stock] : 0) : 0;
            $real_datas['tags']             = $request->tags ? $new_array[$request->tags] : null;
            $real_datas['youtube']          = $request->youtube ? $new_array[$request->youtube] : null;
            $real_datas['policy']           = $request->policy ? $new_array[$request->policy] : null;
            $real_datas['meta_tag']         =  null;
            $real_datas['meta_description'] = $request->meta_description ? $new_array[$request->meta_description] : null;


            try {

                DB::beginTransaction();
                if ($request->thumbnail) {
                    $explode_thumbnail = explode(',', $new_array[$request->thumbnail])[0];

                    $photo_name = Str::random(9) . '.png';
                    $thumbnail = Str::random(10) . '.png';

                    Image::make($explode_thumbnail)
                        ->save('assets/images/thumbnails/' . $thumbnail);
                    Image::make($explode_thumbnail)
                        ->resize(400, 400)
                        ->save('assets/images/products/' . $photo_name);
                    $real_datas['photo'] = $photo_name;
                    $real_datas['thumbnail'] = $thumbnail;
                } else {
                    $explode_photo = explode(',', $request->photo)[0];
                    $photo_name = Str::random(9) . '.png';
                    $thumbnail = Str::random(10) . '.png';
                    Image::make($explode_photo)
                        ->save('assets/images/thumbnails/' . $thumbnail);
                    Image::make($explode_photo)
                        ->resize(400, 400)
                        ->save('assets/images/products/' . $photo_name);
                    $real_datas['photo'] = $photo_name;
                    $real_datas['thumbnail'] = $thumbnail;
                }
            } catch (\Exception $e) {
                $real_datas['photo'] = null;
                $real_datas['thumbnail'] = null;
                DB::rollback();
            }

            if ($prod = Product::where('sku', $real_datas['sku'])->first()) {

                @unlink('assets/images/thumbnails/' . $prod->thumbnail);
                @unlink('assets/images/products/' . $prod->photo);
                Product::where('sku', $real_datas['sku'])->update($real_datas);
            } else {
                Product::create($real_datas);
            }

            DB::commit();
        }

        return redirect()->back()->with('success', 'Product is queued for import, it will take some time to import');
    }


    //*** POST Request
    public function update(Request $request, $id)
    {
        // return $request;
        //--- Validation Section
        $rules = [
            'file'       => 'mimes:zip'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
        }
        //--- Validation Section Ends


        //-- Logic Section
        $data = Product::findOrFail($id);
        $sign = Currency::where('is_default', '=', 1)->first();
        $input = $request->all();

        //Check Types
        if ($request->type_check == 1) {
            $input['link'] = null;
        } else {
            if ($data->file != null) {
                if (file_exists(public_path() . '/assets/files/' . $data->file)) {
                    unlink(public_path() . '/assets/files/' . $data->file);
                }
            }
            $input['file'] = null;
        }


        // Check Physical
        if ($data->type == "Physical") {

            //--- Validation Section
            $rules = ['sku' => 'min:8|unique:products,sku,' . $id];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->getMessageBag()->toArray()));
            }
            //--- Validation Section Ends

            // Check Condition
            if ($request->product_condition_check == "") {
                $input['product_condition'] = 0;
            }

            // Check Shipping Time
            if ($request->shipping_time_check == "") {
                $input['ship'] = null;
            }

            // Check Size

            if (empty($request->size_check)) {
                $input['size'] = null;
                $input['size_qty'] = null;
                $input['size_price'] = null;
            } else {
                if (in_array(null, $request->size) || in_array(null, $request->size_qty) || in_array(null, $request->size_price)) {
                    $input['size'] = null;
                    $input['size_qty'] = null;
                    $input['size_price'] = null;
                } else {
                    $input['size'] = implode(',', $request->size);
                    $input['size_qty'] = implode(',', $request->size_qty);
                    $input['size_price'] = implode(',', $request->size_price);
                }
            }



            // Check Whole Sale
            if (empty($request->whole_check)) {
                $input['whole_sell_qty'] = null;
                $input['whole_sell_discount'] = null;
            } else {
                if (in_array(null, $request->whole_sell_qty) || in_array(null, $request->whole_sell_discount)) {
                    $input['whole_sell_qty'] = null;
                    $input['whole_sell_discount'] = null;
                } else {
                    $input['whole_sell_qty'] = implode(',', $request->whole_sell_qty);
                    $input['whole_sell_discount'] = implode(',', $request->whole_sell_discount);
                }
            }

            // Check Color
            if (empty($request->color_check)) {
                $input['color'] = null;
            } else {
                if (!empty($request->color)) {
                    $input['color'] = implode(',', $request->color);
                }
                if (empty($request->color)) {
                    $input['color'] = null;
                }
            }

            // Check Measure
            if ($request->measure_check == "") {
                $input['measure'] = null;
            }
        }


        // Check Seo
        if (empty($request->seo_check)) {
            $input['meta_tag'] = null;
            $input['meta_description'] = null;
        } else {
            if (!empty($request->meta_tag)) {
                $input['meta_tag'] = implode(',', $request->meta_tag);
            }
        }




        // Check Features
        if (!in_array(null, $request->features) && !in_array(null, $request->colors)) {
            $input['features'] = implode(',', str_replace(',', ' ', $request->features));
            $input['colors'] = implode(',', str_replace(',', ' ', $request->colors));
        } else {
            if (in_array(null, $request->features) || in_array(null, $request->colors)) {
                $input['features'] = null;
                $input['colors'] = null;
            } else {
                $features = explode(',', $data->features);
                $colors = explode(',', $data->colors);
                $input['features'] = implode(',', $features);
                $input['colors'] = implode(',', $colors);
            }
        }

        //Product Tags
        if (!empty($request->tags)) {
            $input['tags'] = implode(',', $request->tags);
        }
        if (empty($request->tags)) {
            $input['tags'] = null;
        }


        $input['price'] = $input['price'] / $sign->value;
        $input['previous_price'] = $input['previous_price'] / $sign->value;

        // store filtering attributes for physical product
        $attrArr = [];
        if (!empty($request->category_id)) {
            $catAttrs = Attribute::where('attributable_id', $request->category_id)->where('attributable_type', 'App\Models\Category')->get();
            if (!empty($catAttrs)) {
                foreach ($catAttrs as $key => $catAttr) {
                    $in_name = $catAttr->input_name;
                    if ($request->has("$in_name")) {
                        $attrArr["$in_name"]["values"] = $request["$in_name"];
                        $attrArr["$in_name"]["prices"] = $request["$in_name" . "_price"];
                        if ($catAttr->details_status) {
                            $attrArr["$in_name"]["details_status"] = 1;
                        } else {
                            $attrArr["$in_name"]["details_status"] = 0;
                        }
                    }
                }
            }
        }

        if (!empty($request->subcategory_id)) {
            $subAttrs = Attribute::where('attributable_id', $request->subcategory_id)->where('attributable_type', 'App\Models\Subcategory')->get();
            if (!empty($subAttrs)) {
                foreach ($subAttrs as $key => $subAttr) {
                    $in_name = $subAttr->input_name;
                    if ($request->has("$in_name")) {
                        $attrArr["$in_name"]["values"] = $request["$in_name"];
                        $attrArr["$in_name"]["prices"] = $request["$in_name" . "_price"];
                        if ($subAttr->details_status) {
                            $attrArr["$in_name"]["details_status"] = 1;
                        } else {
                            $attrArr["$in_name"]["details_status"] = 0;
                        }
                    }
                }
            }
        }
        if (!empty($request->childcategory_id)) {
            $childAttrs = Attribute::where('attributable_id', $request->childcategory_id)->where('attributable_type', 'App\Models\Childcategory')->get();
            if (!empty($childAttrs)) {
                foreach ($childAttrs as $key => $childAttr) {
                    $in_name = $childAttr->input_name;
                    if ($request->has("$in_name")) {
                        $attrArr["$in_name"]["values"] = $request["$in_name"];
                        $attrArr["$in_name"]["prices"] = $request["$in_name" . "_price"];
                        if ($childAttr->details_status) {
                            $attrArr["$in_name"]["details_status"] = 1;
                        } else {
                            $attrArr["$in_name"]["details_status"] = 0;
                        }
                    }
                }
            }
        }



        if (empty($attrArr)) {
            $input['attributes'] = NULL;
        } else {
            $jsonAttr = json_encode($attrArr);
            $input['attributes'] = $jsonAttr;
        }


        if ($data->type != 'Physical') {
            $data->slug = str_slug($data->name, '-') . '-' . strtolower(Str::random(3) . $data->id . Str::random(3));
        } else {
            $data->slug = str_slug($data->name, '-') . '-' . strtolower($data->sku);
        }
        $data->update($input);
        //-- Logic Section Ends

        //--- Redirect Section
        $msg = 'Product Updated Successfully.<a href="' . route('admin-prod-index') . '">View Product Lists.</a>';
        return response()->json($msg);
        //--- Redirect Section Ends
    }


    //*** GET Request
    public function feature($id)
    {
        $data = Product::findOrFail($id);
        return view('admin.product.highlight', compact('data'));
    }

    //*** POST Request
    public function featuresubmit(Request $request, $id)
    {
        //-- Logic Section
        $data = Product::findOrFail($id);
        $input = $request->all();
        if ($request->featured == "") {
            $input['featured'] = 0;
        }
        if ($request->hot == "") {
            $input['hot'] = 0;
        }
        if ($request->best == "") {
            $input['best'] = 0;
        }
        if ($request->top == "") {
            $input['top'] = 0;
        }
        if ($request->latest == "") {
            $input['latest'] = 0;
        }
        if ($request->big == "") {
            $input['big'] = 0;
        }
        if ($request->trending == "") {
            $input['trending'] = 0;
        }
        if ($request->sale == "") {
            $input['sale'] = 0;
        }
        if ($request->is_discount == "") {
            $input['is_discount'] = 0;
            $input['discount_date'] = null;
        }

        $data->update($input);
        //-- Logic Section Ends

        //--- Redirect Section
        $msg = 'Highlight Updated Successfully.';
        return response()->json($msg);
        //--- Redirect Section Ends

    }

    //*** GET Request
    public function destroy($id)
    {

        $data = Product::findOrFail($id);
        if ($data->galleries->count() > 0) {
            foreach ($data->galleries as $gal) {
                if (file_exists(public_path() . '/assets/images/galleries/' . $gal->photo)) {
                    unlink(public_path() . '/assets/images/galleries/' . $gal->photo);
                }
                $gal->delete();
            }
        }

        if ($data->reports->count() > 0) {
            foreach ($data->reports as $gal) {
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
            if (file_exists(public_path() . '/assets/images/products/' . $data->photo)) {
                unlink(public_path() . '/assets/images/products/' . $data->photo);
            }
        }

        if (file_exists(public_path() . '/assets/images/thumbnails/' . $data->thumbnail) && $data->thumbnail != "") {
            unlink(public_path() . '/assets/images/thumbnails/' . $data->thumbnail);
        }

        if ($data->file != null) {
            if (file_exists(public_path() . '/assets/files/' . $data->file)) {
                unlink(public_path() . '/assets/files/' . $data->file);
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
