<?php

namespace App\Http\Controllers\User;

use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class StripeController extends Controller
{

    public function __construct()
    {
        //Set Spripe Keys
        $stripe = Generalsetting::findOrFail(1);
        Config::set('services.stripe.key', $stripe->stripe_key);
        Config::set('services.stripe.secret', $stripe->stripe_secret);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'shop_name'   => 'unique:users',
        ], [
            'shop_name.unique' => 'This shop name has already been taken.'
        ]);
        $user = Auth::user();
        $subs = Subscription::findOrFail($request->subs_id);
        $settings = Generalsetting::findOrFail(1);
        $item_amount = $subs->price;
        $item_currency = $subs->currency_code;

        try {
            $stripe_secret_key = Config::get('services.stripe.secret');
            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $checkout_session = \Stripe\Checkout\Session::create([
                "mode" => "payment",
                "success_url" => route('user.stripe.notify') . '?session_id={CHECKOUT_SESSION_ID}',
                "cancel_url" => route('user.payment.cancle'),
                "customer_email" => $user->email,
                "locale" => "auto",
                "line_items" => [
                    [
                        "quantity" => 1,
                        "price_data" => [
                            "currency" => $item_currency,
                            "unit_amount" => $item_amount * 100,
                            "product_data" => [
                                "name" => $settings->title . ' ' . $subs->title . ' Plan',
                            ]
                        ]
                    ],
                ]
            ]);

            Session::put('subscription_data', $request->subs_id);
            Session::put('input_data', $request->all());
            return redirect($checkout_session->url);
        } catch (Exception $e) {
            return back()->with('unsuccess', $e->getMessage());
        }
    }


    public function notify(Request $request)
    {
        $subid = Session::get('subscription_data');
        $user = Auth::user();
        $input = Session::get('input_data') ? Session::get('input_data') : [];
        $stripe = new \Stripe\StripeClient(Config::get('services.stripe.secret'));
        $response = $stripe->checkout->sessions->retrieve($request->session_id);
        $package = $user->subscribes()->where('status', 1)->orderBy('id', 'desc')->first();
        $subs = Subscription::findOrFail($subid);
        $settings = Generalsetting::findOrFail(1);

        if ($response->status == 'complete') {

            $today = Carbon::now()->format('Y-m-d');

            $user->is_vendor = 2;
            if (!empty($package)) {
                if ($package->subscription_id == $subid) {
                    $newday = strtotime($today);
                    $lastday = strtotime($user->date);
                    $secs = $lastday - $newday;
                    $days = $secs / 86400;
                    $total = $days + $subs->days;
                    $user->date = date('Y-m-d', strtotime($today . ' + ' . $total . ' days'));
                } else {
                    $user->date = date('Y-m-d', strtotime($today . ' + ' . $subs->days . ' days'));
                }
            } else {
                $user->date = date('Y-m-d', strtotime($today . ' + ' . $subs->days . ' days'));
            }
            $user->mail_sent = 1;
            $user->update($input);
            $sub = new UserSubscription;
            $sub->user_id = $user->id;
            $sub->subscription_id = $subs->id;
            $sub->title = $subs->title;
            $sub->currency = $subs->currency;
            $sub->currency_code = $subs->currency_code;
            $sub->price = $subs->price;
            $sub->days = $subs->days;
            $sub->allowed_products = $subs->allowed_products;
            $sub->details = $subs->details;
            $sub->method = 'Stripe';
            $sub->txnid = $response->payment_intent;
            $sub->status = 1;
            $sub->save();
            if ($settings->is_smtp == 1) {
                $data = [
                    'to' => $user->email,
                    'type' => "vendor_accept",
                    'cname' => $user->name,
                    'oamount' => "",
                    'aname' => "",
                    'aemail' => "",
                    'onumber' => "",
                ];
                $mailer = new GeniusMailer();
                $mailer->sendAutoMail($data);
            } else {
                $headers = "From: " . $settings->from_name . "<" . $settings->from_email . ">";
                mail($user->email, 'Your Vendor Account Activated', 'Your Vendor Account Activated Successfully. Please Login to your account and build your own shop.', $headers);
            }

            return redirect()->route('user-dashboard')->with('success', 'Vendor Account Activated Successfully');
        }
    }
}
