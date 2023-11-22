@extends('layouts.front')
@section('content')


<section class="user-dashbord">
    <div class="container">
      <div class="row">
        @include('includes.user-dashboard-sidebar')
        <div class="col-lg-8">
          @include('includes.form-success')
          <div class="user-profile-details">
            <div class="account-info">
              <div class="header-area">
                <h4 class="title">
                  {{ $langg->lang208 }}
                </h4>
              </div>
              <div class="edit-info-area">
              </div>
              <div class="main-info">
                <h5 class="title">{{ $user->name }}</h5>
                <ul class="list">
                  <li>
                    <p><span class="user-title">{{ $langg->lang209 }}:</span> {{ $user->email }}</p>
                  </li>
                  @if($user->phone != null)
                  <li>
                    <p><span class="user-title">{{ $langg->lang210 }}:</span> {{ $user->phone }}</p>
                  </li>
                  @endif
                  @if($user->fax != null)
                  <li>
                    <p><span class="user-title">{{ $langg->lang211 }}:</span> {{ $user->fax }}</p>
                  </li>
                  @endif
                  @if($user->city != null)
                  <li>
                    <p><span class="user-title">{{ $langg->lang212 }}:</span> {{ $user->city }}</p>
                  </li>
                  @endif
                  @if($user->zip != null)
                  <li>
                    <p><span class="user-title">{{ $langg->lang213 }}:</span> {{ $user->zip }}</p>
                  </li>
                  @endif
                  @if($user->address != null)
                  <li>
                    <p><span class="user-title">{{ $langg->lang214 }}:</span> {{ $user->address }}</p>
                  </li>
                  @endif
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>



@endsection