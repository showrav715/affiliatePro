<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\SheduleItem;
use Carbon\Carbon;
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

class SheduleItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {


        try {
            $sheduleItems = SheduleItem::WhereDate('repeat_time', Carbon::today())
                ->orWhere(function ($query) {
                    $query->where('total_reapeat', 0)
                        ->whereDate('shedule_date', Carbon::today());
                })
                ->get();

            if (count($sheduleItems) == 0) {
                return;
            }
            foreach ($sheduleItems as $sheduleItem) {

                if ($sheduleItem->type == 'link') {
                    $path = file_get_contents($sheduleItem->link);
                    $csv = array_map('str_getcsv', explode("\n", $path));
                    Log::info('shedule item link');
                } else {
                    $csv = array_map('str_getcsv', file(base_path('../') . 'assets/temp/' . $sheduleItem->file_name));
                }


                $requestData = json_decode($sheduleItem->request_data);
                $chunks = array_chunk($csv, 100);
                $header = [];
                foreach ($chunks as $key => $chunk) {
                    $data = $chunk;
                    if ($key == 0) {
                        $header = $data[0];
                        unset($data[0]);
                    }
                    $this->process($header, $data, $requestData, $sheduleItem->vendor_id);
                }

                $sheduleItem->total_reapeat = $sheduleItem->total_reapeat + 1;

                if ($sheduleItem->repeat_type == 'daily') {
                    $sheduleItem->repeat_time = Carbon::now()->addDay(1);
                } elseif ($sheduleItem->repeat_type == 'weekly') {
                    $sheduleItem->repeat_time = Carbon::now()->addWeek(1);
                } elseif ($sheduleItem->repeat_type == 'monthly') {
                    $sheduleItem->repeat_time = Carbon::now()->addMonth(1);
                } elseif ($sheduleItem->repeat_type == 'yearly') {
                    $sheduleItem->repeat_time = Carbon::now()->addYear(1);
                }

                $sheduleItem->update();

                Log::info('shedule item updated');
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }


    public function process($header, $data, $request, $vendor_id)
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

            if ($vendor_id) {
                $real_datas['user_id'] = $vendor_id;
            }

            if ($prod = Product::where('sku', $real_datas['sku'])->first()) {
                unlink(base_path('../') .'assets/images/thumbnails/' . $prod->thumbnail);
                @unlink(base_path('../') .'assets/images/thumbnails/' . $prod->thumbnail);
                @unlink(base_path('../') .'assets/images/products/' . $prod->photo);
                Product::where('sku', $real_datas['sku'])->update($real_datas);
            } else {
                Product::create($real_datas);
            }

            DB::commit();
        }
    }
}
