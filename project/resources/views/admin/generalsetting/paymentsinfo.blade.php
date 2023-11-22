@extends('layouts.admin')


@section('styles')

<style type="text/css">
  .img-upload #image-preview {
    background-size: unset !important;
  }
</style>

@endsection

@section('content')

<div class="content-area">
  <div class="mr-breadcrumb">
    <div class="row">
      <div class="col-lg-12">
        <h4 class="heading">{{ __('Payment Informations') }}</h4>
        <ul class="links">
          <li>
            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
          </li>
          <li>
            <a href="javascript:;">{{ __('Payment Settings') }}</a>
          </li>
          <li>
            <a href="{{ route('admin-gs-payments') }}">{{ __('Payment Informations') }}</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="add-product-content social-links-area">
    <div class="row">
      <div class="col-lg-12">
        <div class="product-description">
          <div class="body-area">
            <div class="gocover"
              style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
            </div>
            <form action="{{ route('admin-gs-update-payment') }}" id="geniusform" method="POST"
              enctype="multipart/form-data">
              {{ csrf_field() }}

              @include('includes.admin.form-both')


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">
                      {{ __('Stripe') }}
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="action-list">
                    <select
                      class="process select droplinks {{ $gs->stripe_check == 1 ? 'drop-success' : 'drop-danger' }}">
                      <option data-val="1" value="{{route('admin-gs-stripe',1)}}" {{ $gs->stripe_check == 1 ? 'selected'
                        : '' }}>{{ __('Activated') }}</option>
                      <option data-val="0" value="{{route('admin-gs-stripe',0)}}" {{ $gs->stripe_check == 0 ? 'selected'
                        : '' }}>{{ __('Deactivated') }}</option>
                    </select>
                  </div>
                </div>
              </div>


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Stripe Key') }} *
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <input type="text" class="input-field" placeholder="{{ __('Stripe Key') }}" name="stripe_key"
                    value="{{ $gs->stripe_key }}" required="">
                </div>
              </div>

              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Stripe Secret') }} *
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <input type="text" class="input-field" placeholder="{{ __('Stripe Secret') }}" name="stripe_secret"
                    value="{{ $gs->stripe_secret }}" required="">
                </div>
              </div>



              <hr>


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">
                      {{ __('Paypal') }}
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="action-list">
                    <select
                      class="process select droplinks {{ $gs->paypal_check == 1 ? 'drop-success' : 'drop-danger' }}">
                      <option data-val="1" value="{{route('admin-gs-paypal',1)}}" {{ $gs->paypal_check == 1 ? 'selected'
                        : '' }}>{{ __('Activated') }}</option>
                      <option data-val="0" value="{{route('admin-gs-paypal',0)}}" {{ $gs->paypal_check == 0 ? 'selected'
                        : '' }}>{{ __('Deactivated') }}</option>
                    </select>
                  </div>
                </div>
              </div>


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Paypal Public Key') }} *
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <input type="text" class="input-field" placeholder="{{ __('Paypal Public Key') }}"
                    name="paypal_public_key" value="{{ $gs->paypal_public_key }}" required="">
                </div>
              </div>

              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Paypal Secret Key') }} *
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <input type="text" class="input-field" placeholder="{{ __('Paypal Secret Key') }}"
                    name="paypal_secret_key" value="{{ $gs->paypal_secret_key }}" required="">
                </div>
              </div>




              <hr>



              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">
                      {{ __('Paystack') }}
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="action-list">
                    <select
                      class="process select droplinks {{ $gs->is_paystack == 1 ? 'drop-success' : 'drop-danger' }}">
                      <option data-val="1" value="{{route('admin-gs-paystack',1)}}" {{ $gs->is_paystack == 1 ?
                        'selected' : '' }}>{{ __('Activated') }}</option>
                      <option data-val="0" value="{{route('admin-gs-paystack',0)}}" {{ $gs->is_paystack == 0 ?
                        'selected' : '' }}>{{ __('Deactivated') }}</option>
                    </select>
                  </div>
                </div>
              </div>


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Paystack Public Key') }} *
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <input type="text" class="input-field" placeholder="{{ __('Paystack Public Key') }}"
                    name="paystack_key" value="{{ $gs->paystack_key }}" required="">
                </div>
              </div>

              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Paystack Business Email') }} *
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <input type="text" class="input-field" placeholder="{{ __('Paystack Business Email') }}"
                    name="paystack_email" value="{{ $gs->paystack_email }}" required="">
                </div>
              </div>

              <hr>

              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">
                      {{ __('Razorpay') }}
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="action-list">
                    <select
                      class="process select droplinks {{ $gs->is_razorpay == 1 ? 'drop-success' : 'drop-danger' }}">
                      <option data-val="1" value="{{route('admin-gs-razor',1)}}" {{ $gs->is_razorpay == 1 ? 'selected' :
                        '' }}>{{ __('Activated') }}</option>
                      <option data-val="0" value="{{route('admin-gs-razor',0)}}" {{ $gs->is_razorpay == 0 ? 'selected' :
                        '' }}>{{ __('Deactivated') }}</option>
                    </select>
                  </div>
                </div>
              </div>




              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Razorpay Key') }} *</h4>

                  </div>
                </div>
                <div class="col-lg-6">
                  <textarea class="input-field" name="razorpay_key" placeholder="{{ __('Razorpay Key') }}"
                    required>{{ $gs->razorpay_key }}</textarea>

                </div>
              </div>



              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Razorpay Secret') }} *</h4>

                  </div>
                </div>
                <div class="col-lg-6">
                  <textarea class="input-field" name="razorpay_secret" placeholder="{{ __('Razorpay Key') }}"
                    required>{{ $gs->razorpay_secret }}</textarea>

                </div>
              </div>

              <hr>


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">
                      {{ __('Authorize.Net') }}
                    </h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="action-list">
                    <select
                      class="process select droplinks {{ $gs->is_authorize == 1 ? 'drop-success' : 'drop-danger' }}">
                      <option data-val="1" value="{{route('admin-gs-authorize',1)}}" {{ $gs->is_authorize == 1 ?
                        'selected' : '' }}>{{ __('Activated') }}</option>
                      <option data-val="0" value="{{route('admin-gs-authorize',0)}}" {{ $gs->is_authorize == 0 ?
                        'selected' : '' }}>{{ __('Deactivated') }}</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Authorize.Net API LOGIN ID') }} *</h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <textarea class="input-field" name="authorize_login_id"
                    placeholder="{{ __('Authorize.Net API LOGIN ID') }}"
                    required>{{ $gs->authorize_login_id }}</textarea>
                </div>
              </div>

              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Authorize.Net TRANSACTION KEY') }} *</h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <textarea class="input-field" name="authorize_txn_key"
                    placeholder="{{ __('Authorize.Net TRANSACTION KEY') }}"
                    required>{{ $gs->authorize_txn_key }}</textarea>
                </div>
              </div>

              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Authorize.Net Sandbox Check') }} *
                    </h4>
                  </div>
                </div>

                <div class="col-lg-6">
                  <label class="switch">
                    <input type="checkbox" name="authorize_mode" value="1" {{ $gs->authorize_mode == 'SANDBOX' ?
                    "checked":"" }}>
                    <span class="slider round"></span>
                  </label>
                </div>
              </div>

          
            
              <hr>


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">
                    <h4 class="heading">{{ __('Currency Format') }} *</h4>
                  </div>
                </div>
                <div class="col-lg-6">
                  <select name="currency_format" required="">
                    <option value="0" {{ $gs->currency_format == 0 ? 'selected' : '' }}>{{__('Before Price')}}</option>
                    <option value="1" {{ $gs->currency_format == 1 ? 'selected' : '' }}>{{ __('After Price') }}</option>
                  </select>
                </div>
              </div>


              <div class="row justify-content-center">
                <div class="col-lg-3">
                  <div class="left-area">

                  </div>
                </div>
                <div class="col-lg-6">
                  <button class="addProductSubmit-btn" type="submit">{{ __('Save') }}</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection