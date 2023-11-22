<?php

namespace App\Http\Controllers\User;


use App\Classes\GeniusMailer;
use App\Models\Generalsetting;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Omnipay\Omnipay;
use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PaypalController extends Controller
{
    public $gateway;
    public function __construct()
    {
        $gs = Generalsetting::findOrFail(1);
        $this->gateway = Omnipay::create('PayPal_Rest');
        $this->gateway->setClientId($gs['paypal_public_key']);
        $this->gateway->setSecret($gs['paypal_secret_key']);
        $this->gateway->setTestMode(true);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'shop_name'   => 'unique:users',
        ], [
            'shop_name.unique' => 'This shop name has already been taken.'
        ]);

        $subs = Subscription::findOrFail($request->subs_id);

        $cancel_url = action('User\PaypalController@paycancle');
        $notify_url = action('User\PaypalController@notify');
        $item_amount = $subs->price;

        try {
            $response = $this->gateway->purchase(array(
                'amount' => $item_amount,
                'currency' => $subs->currency_code,
                'returnUrl' => $notify_url,
                'cancelUrl' => $cancel_url,
            ))->send();

            if ($response->isRedirect()) {
                Session::put('sub_id', $subs->id);
                if ($response->redirect()) {

                    return redirect($response->redirect());
                }
            } else {
                return redirect()->back()->with('unsuccess', $response->getMessage());
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('unsuccess', $th->getMessage());
        }
    }


    public function paycancle()
    {
        return redirect()->back()->with('unsuccess', 'Payment Cancelled.');
    }

    public function payreturn()
    {
        return redirect()->route('user-dashboard')->with('success', 'Vendor Account Activated Successfully');
    }


    public function notify(Request $request)
    {

        $sub_id = Session::get('sub_id');
        $success_url = route('user.payment.return');
        $cancel_url = route('user.payment.cancle');
        $input = $request->all();

        $responseData = $request->all();

        $user = Auth::user();

        $package = $user->subscribes()->where('status', 1)->orderBy('id', 'desc')->first();
        $subs = Subscription::findOrFail($sub_id);
        $settings = Generalsetting::findOrFail(1);
        $responseData = $request->all();
        if (empty($responseData['PayerID']) || empty($responseData['token'])) {
            return redirect($cancel_url)->with('unsuccess', 'Payment Unsuccessfull');
        }
        $transaction = $this->gateway->completePurchase(array(
            'payer_id' => $responseData['PayerID'],
            'transactionReference' => $responseData['paymentId'],
        ));

        $response = $transaction->send();

        if ($response->isSuccessful()) {


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
            $sub->method = 'Paypal';
            $sub->txnid = $response->getData()['transactions'][0]['related_resources'][0]['sale']['id'];
            $sub->status = 1;
            $sub->save();

            $today = Carbon::now()->format('Y-m-d');
            $input = $request->all();
            $user->is_vendor = 2;
            if (!empty($package)) {
                if ($package->subscription_id == $request->subs_id) {
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





            if ($settings->is_smtp == 1) {
                $maildata = [
                    'to' => $user->email,
                    'type' => "vendor_accept",
                    'cname' => $user->name,
                    'oamount' => "",
                    'aname' => "",
                    'aemail' => "",
                    'onumber' => "",
                ];
                $mailer = new GeniusMailer();
                $mailer->sendAutoMail($maildata);
            } else {
                $headers = "From: " . $settings->from_name . "<" . $settings->from_email . ">";
                mail($user->email, 'Your Vendor Account Activated', 'Your Vendor Account Activated Successfully. Please Login to your account and build your own shop.', $headers);
            }

            return redirect($success_url);
        } else {
            return redirect($cancel_url);
        }
    }
}
