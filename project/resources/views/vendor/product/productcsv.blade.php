@extends('layouts.vendor')
@section('styles')

<link href="{{asset('assets/admin/css/product.css')}}" rel="stylesheet" />

@endsection
@section('content')

<div class="content-area">
	<div class="mr-breadcrumb">
		<div class="row">
			<div class="col-lg-12">
				<h4 class="heading">{{ __("Csv Bulk Upload") }}</h4>
				<ul class="links">
					<li>
						<a href="{{ route('vendor-dashboard') }}">{{ __("Dashboard") }} </a>
					</li>

					<li>
						<a href="javascript:;">{{ __("Csv Bulk Upload") }}</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="add-product-content">
		<div class="row">
			<div class="col-lg-12 p-5">

				<div class="gocover"
					style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
				</div>
				<form id="" action="{{route('vendor-prod-checksubmit')}}" method="POST" enctype="multipart/form-data">
					{{csrf_field()}}

					@include('includes.admin.form-both')
					@include('includes.form-error')
					@include('includes.form-success')

					<div class="row">
						<div class="col-lg-4">
							<div class="left-area">
								<h4 class="heading">{{ __('Select Type') }} *</h4>
								<p class="sub-heading">{{ __('(In Any Language)') }}</p>
							</div>
						</div>
						<div class="col-lg-7">
							<select name="type" id="csv_upload_type" class="input-field">
								<option value="file">{{ __('File') }}</option>
								<option value="link">{{ __('Link') }}</option>
							</select>
						</div>
					</div>

					{{--
					@php
					$link =
					'https://www.webtoffee.com/wp-content/uploads/2021/05/Basic-Product_WooCommerce_Sample_CSV.csv';
					$path = file_get_contents($link);
					$csv = array_map('str_getcsv', explode("\n", $path));
					$chunks = array_chunk($csv, 100);

					$file = array_map('str_getcsv', file(base_path('../') . 'assets/temp/LlBsSMrx.csv'));
					$chunkse = array_chunk($file, 100);
					@endphp
					--}}


					<div class="row">
						<div class="col-lg-4">
							<div class="left-area">
								<h4 class="heading">{{ __('Csv Download Link') }} *</h4>
								<p class="sub-heading">{{ __('(In Any Language)') }}</p>
							</div>
						</div>
						<div class="col-lg-7">
							<input type="text" class="input-field d-none" id="csv_link" name="link"
								placeholder="{{ __('Csv Download Link') }}" value="">
						</div>
					</div>


					<div class="row justify-content-center " id="csv_file">
						<div class="col-lg-12 d-flex justify-content-center text-center">
							<div class="csv-icon">
								<i class="fas fa-file-csv"></i>
							</div>
						</div>
						<div class="col-lg-12 d-flex justify-content-center text-center">
							<div class="left-area mr-4">
								<h4 class="heading">{{ __("Upload a File") }} *</h4>
							</div>
							<span class="file-btn">
								<input type="file" id="csvfile" name="csvfile" accept=".csv">
							</span>
						</div>
					</div>


					<div class="row">
						<div class="col-lg-12 mt-4 text-center">
							<button class="mybtn1 mr-5" type="submit">{{ __("Start Import") }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>


@endsection

@section('scripts')

<script src="{{asset('assets/admin/js/product.js')}}"></script>

<script>
	$(document).ready(function () {
		$("#csv_upload_type").on('change', function () {
			var type = $(this).val();
			if (type == 'file') {
				$("#csv_file").removeClass('d-none');
				$("#csv_link").addClass('d-none');
			} else {
				$("#csv_file").addClass('d-none');
				$("#csv_link").removeClass('d-none');
			}
		});
	});
</script>



@endsection