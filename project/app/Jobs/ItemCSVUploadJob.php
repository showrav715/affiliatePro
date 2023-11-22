<?php

namespace App\Jobs;

use App\Models\Product;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Image;

class ItemCSVUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $header;
    public $data;
    public $request;
    public $vendor_id;
    /**
     * Create a new job instance.
     */
    public function __construct($header, $data, $request, $vendor_id = null)
    {
        $this->header = $header;
        $this->data = $data;
        $this->request = $request;
        $this->vendor_id = $vendor_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $data = $this->data;
            $request = json_decode($this->request);
            $header = $this->header;

            $real_datas = [];
            foreach ($data as $key => $check) {
                $new_array = array_combine($header, $check);
                $real_datas['category_id']      = @$request->category_id;
                $real_datas['subcategory_id']   = @$request->subcategory_id;
                $real_datas['childcategory_id'] = @$request->childcategory_id;
                $real_datas['sku']              = @$new_array[$request->sku];
                $real_datas['affiliate_link']   = @$request->affiliate_link ? isset($new_array[$request->affiliate_link]) : $request->affiliate_link;
                $real_datas['name']             = @$new_array[$request->product_name];
                $real_datas['slug']             = @Str::slug($new_array[$request->product_name] . $new_array[$request->sku]);
                $real_datas['size']             = @$request->sizes ? $new_array[$request->sizes] : null;
                $real_datas['size_qty']         = @$request->size_quantity ? $new_array[$request->size_quantity] : null;
                $real_datas['size_price']       = @$request->size_extra_price ? $new_array[$request->size_extra_price] : null;
                $real_datas['color']            = @$request->colors ? $new_array[$request->colors] : null;
                $real_datas['price']            = @$request->current_price ? (is_numeric($new_array[$request->current_price]) ? $new_array[$request->current_price] : 0) : null;
                $real_datas['previous_price']   = @$request->previous_price ? (is_numeric($new_array[$request->previous_price]) ? $new_array[$request->previous_price] : 0) : null;
                $real_datas['details']          = @$request->product_description ? utf8_encode($new_array[$request->product_description]) : null;
                $real_datas['stock']            = @$request->stock ?  (is_numeric($new_array[$request->stock]) ? $new_array[$request->stock] : 0) : 0;
                $real_datas['tags']             = @$request->tags ? $new_array[$request->tags] : null;
                $real_datas['youtube']          = @$request->youtube ? $new_array[$request->youtube] : null;
                $real_datas['policy']           = @$request->policy ? $new_array[$request->policy] : null;
                $real_datas['meta_tag']         =  null;
                $real_datas['meta_description'] = @$request->meta_description ? $new_array[$request->meta_description] : null;


                try {

                    DB::beginTransaction();
                    if ($request->thumbnail) {
                        $explode_thumbnail = explode(',', $new_array[$request->thumbnail])[0];

                        $photo_name = Str::random(9) . '.png';
                        $thumbnail = Str::random(10) . '.png';

                        Image::make($explode_thumbnail)
                            ->save(base_path('../') . 'assets/images/thumbnails/' . $thumbnail);
                        Image::make($explode_thumbnail)
                            ->resize(400, 400)
                            ->save(base_path('../') . 'assets/images/products/' . $photo_name);
                        $real_datas['photo'] = $photo_name;
                        $real_datas['thumbnail'] = $thumbnail;
                    } else {
                        $explode_photo = explode(',', $request->photo)[0];
                        $photo_name = Str::random(9) . '.png';
                        $thumbnail = Str::random(10) . '.png';
                        Image::make($explode_photo)
                            ->save(base_path('../') . 'assets/images/thumbnails/' . $thumbnail);
                        Image::make($explode_photo)
                            ->resize(400, 400)
                            ->save(base_path('../') . 'assets/images/products/' . $photo_name);
                        $real_datas['photo'] = $photo_name;
                        $real_datas['thumbnail'] = $thumbnail;
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                    $real_datas['photo'] = null;
                    $real_datas['thumbnail'] = null;
                    DB::rollback();
                    Log::error($e->getMessage());
                }

                if ($this->vendor_id) {
                    $real_datas['user_id'] = $this->vendor_id;
                }

                if ($prod = Product::where('sku', $real_datas['sku'])->first()) {
                    @unlink(base_path('../') .'assets/images/thumbnails/' . $prod->thumbnail);
                    @unlink(base_path('../') .'assets/images/products/' . $prod->photo);
                    Product::where('sku', $real_datas['sku'])->update($real_datas);
                } else {
                    Product::create($real_datas);
                }

                DB::commit();
            }
            Log::info('Product uploaded successfully');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
