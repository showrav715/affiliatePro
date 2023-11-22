<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Generalsetting;
use App\Models\Currency;
use Illuminate\Support\Facades\Session;

class Product extends Model
{

    protected $fillable = ['user_id','stock','category_id', 'product_type', 'affiliate_link', 'sku', 'subcategory_id', 'childcategory_id', 'name', 'photo', 'size', 'size_qty', 'size_price', 'color', 'details', 'price', 'previous_price', 'policy', 'status', 'views', 'tags', 'featured', 'best', 'top', 'hot', 'latest', 'big', 'trending', 'sale', 'features', 'colors', 'product_condition', 'ship', 'meta_tag', 'meta_description', 'youtube', 'measure', 'discount_date', 'is_discount', 'slug'];

    public static function filterProducts($collection)
    {
        foreach ($collection as $key => $data) {
            if ($data->user_id != 0) {
                if ($data->user->is_vendor != 2) {
                    unset($collection[$key]);
                }
            }
            if (isset($_GET['max'])) {
                if ($data->vendorSizePrice() >= $_GET['max']) {
                    unset($collection[$key]);
                }
            }
            $data->price = $data->vendorSizePrice();
        }
        return $collection;
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category')->withDefault();
    }

    public function subcategory()
    {
        return $this->belongsTo('App\Models\Subcategory')->withDefault();
    }

    public function childcategory()
    {
        return $this->belongsTo('App\Models\Childcategory')->withDefault();
    }

    public function galleries()
    {
        return $this->hasMany('App\Models\Gallery');
    }

    public function ratings()
    {
        return $this->hasMany('App\Models\Rating');
    }

    public function wishlists()
    {
        return $this->hasMany('App\Models\Wishlist');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }

    public function clicks()
    {
        return $this->hasMany('App\Models\ProductClick');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function reports()
    {
        return $this->hasMany('App\Models\Report', 'user_id');
    }

    public function vendorPrice()
    {
        $gs = Generalsetting::findOrFail(1);
        $price = $this->price;
        if ($this->user_id != 0) {
            $price = $this->price + $gs->fixed_commission + ($this->price / 100) * $gs->percentage_commission;
        }

        return $price;
    }

    public function vendorSizePrice()
    {
        $gs = Generalsetting::findOrFail(1);
        $price = $this->price;
        if ($this->user_id != 0) {
            $price = $this->price + $gs->fixed_commission + ($this->price / 100) * $gs->percentage_commission;
        }
        if (!empty($this->size)) {
            $price += $this->size_price[0];
        }

        return $price;
    }


    public  function setCurrency()
    {
        $gs = Generalsetting::findOrFail(1);
        $price = $this->price;
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }
        $price = round($price * $curr->value, 2);
        if ($gs->currency_format == 0) {
            return $curr->sign . $price;
        } else {
            return $price . $curr->sign;
        }
    }


    public function showPrice()
    {
        $gs = Generalsetting::findOrFail(1);
        $price = $this->price;

        if ($this->user_id != 0) {
            $price = $this->price + $gs->fixed_commission + ($this->price / 100) * $gs->percentage_commission;
        }

        if (!empty($this->size)) {
            $price += $this->size_price[0];
        }

        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }



        $price = round(($price) * $curr->value, 2);
        if ($gs->currency_format == 0) {
            return $curr->sign . $price;
        } else {
            return $price . $curr->sign;
        }
    }

    public function showPreviousPrice()
    {
        $gs = Generalsetting::findOrFail(1);
        $price = $this->previous_price;
        if (!$price) {
            return '';
        }
        if ($this->user_id != 0) {
            $price = $this->previous_price + $gs->fixed_commission + ($this->previous_price / 100) * $gs->percentage_commission;
        }

        if (!empty($this->size)) {
            $price += $this->size_price[0];
        }

      
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }
        $price = round($price * $curr->value, 2);
        if ($gs->currency_format == 0) {
            return $curr->sign . $price;
        } else {
            return $price . $curr->sign;
        }
    }

    public static function convertPrice($price)
    {
        $gs = Generalsetting::findOrFail(1);
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }
        $price = round($price * $curr->value, 2);
        if ($gs->currency_format == 0) {
            return $curr->sign . $price;
        } else {
            return $price . $curr->sign;
        }
    }

    public static function vendorConvertPrice($price)
    {
        $gs = Generalsetting::findOrFail(1);

        $curr = Currency::where('is_default', '=', 1)->first();
        $price = round($price * $curr->value, 2);
        if ($gs->currency_format == 0) {
            return $curr->sign . $price;
        } else {
            return $price . $curr->sign;
        }
    }

    public static function convertPreviousPrice($price)
    {
        $gs = Generalsetting::findOrFail(1);
        if (Session::has('currency')) {
            $curr = Currency::find(Session::get('currency'));
        } else {
            $curr = Currency::where('is_default', '=', 1)->first();
        }
        $price = round($price * $curr->value, 2);
        if ($gs->currency_format == 0) {
            return $curr->sign . $price;
        } else {
            return $price . $curr->sign;
        }
    }

    public function showName()
    {
        $name = strlen($this->name) > 55 ? substr($this->name, 0, 55) . '...' : $this->name;
        return $name;
    }


    public function emptyStock()
    {
        $stck = (string)$this->stock;
        if ($stck == "0") {
            return true;
        }
    }

    public static function showTags()
    {
        $tags = null;
        $tagz = '';
        $name = Product::where('status', '=', 1)->pluck('tags')->toArray();
        foreach ($name as $nm) {
            if (!empty($nm)) {
                foreach ($nm as $n) {
                    $tagz .= $n . ',';
                }
            }
        }
        $tags = array_unique(explode(',', $tagz));
        return $tags;
    }


    public function getSizeAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    public function getSizeQtyAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    public function getSizePriceAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    public function getColorAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    public function getTagsAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    public function getMetaTagAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    public function getFeaturesAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    public function getColorsAttribute($value)
    {
        if ($value == null) {
            return '';
        }
        return explode(',', $value);
    }

    
}
