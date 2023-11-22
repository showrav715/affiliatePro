@extends('layouts.admin')

@section('content')

<div class="content-area">
    <div class="mr-breadcrumb">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="heading">{{ __("CSV IMPORT") }} </h4>
                <ul class="links">
                    <li>
                        <a href="{{ route('admin.dashboard') }}">{{ __("Dashboard") }} </a>
                    </li>
                    <li>
                        <a href="javascript:;">{{ __("Products") }} </a>
                    </li>
                    <li>
                        <a href="{{ route('admin-import-create') }}">{{ __("Add Product") }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="add-product-content">
        <div class="row">
            <div class="col-md-12 col-md-offset-2 p-5">
                <div class="panel panel-default">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4">

                                    <ul class="list-group">
                                        @php
                                        $fields = config('app.db_fields');
                                        @endphp
                                        @foreach ($fields as $db_field)
                                        <li data-product="{{$db_field}}" class="list-group-item dragable">
                                            {{$db_field}}</li>
                                        @endforeach
                                    </ul>

                                </div>
                                <div class="col-8">
                                    <form action="{{route('admin-prod-importsubmit')}}" method="POST">
                                        @csrf
                                        <input type="text" name="file_name" value="{{$file_name}}">
                                        <input type="text" name="type" value="{{$type}}">
                                        <input type="text" name="link" value="{{$link}}">

                                        <div id="droppable">
                                            <div class="form-group">
                                                <label for="category_id">{{ __('Product Category') }}</label>
                                                <select id="cat" name="category_id" required="">
                                                    <option value="">{{ __("Select Category") }}</option>
                                                    @foreach(DB::table('categories')->get() as $cat)
                                                    <option data-href="{{ route('admin-subcat-load',$cat->id) }}"
                                                        value="{{ $cat->id }}">{{$cat->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>


                                            <div class="form-group">
                                                <label for="subcategory_id">{{ __('Product Subcategory') }}</label>
                                                <select id="subcat" name="subcategory_id" disabled="">
                                                    <option value="">{{ __("Select Sub Category") }}</option>
                                                </select>
                                            </div>


                                            <div class="form-group">
                                                <label for="child_category_id">{{ __('Product Childcategory')
                                                    }}</label>
                                                <select id="childcat" name="childcategory_id" disabled="">
                                                    <option value="">{{ __("Select Child Category") }}</option>
                                                </select>
                                            </div>


                                            <div class="form-group">
                                                <label for="sku">{{ __('Product Sku') }}</label>
                                                <input type="text" class="input-field" id="sku" name="sku"
                                                    placeholder="{{ __('Product Sku') }}" value="" required>
                                            </div>


                                            <div class="form-group">
                                                <label for="product_name">{{ __('Product Name') }}</label>
                                                <input type="text" class="input-field" id="product_name"
                                                    name="product_name" placeholder="{{ __('Product Name') }}" value=""
                                                    required>
                                            </div>


                                            <div class="form-group">
                                                <label for="photo">{{ __('Feature Image') }}</label>
                                                <input type="text" class="input-field" id="photo" name="photo"
                                                    placeholder="{{ __('Feature Image') }}" value="" required>
                                            </div>


                                            <div class="form-group">
                                                <label for="thumbnail">{{ __('Product Thumbnail') }}</label>
                                                <input type="text" class="input-field" id="thumbnail" name="thumbnail"
                                                    placeholder="{{ __('Product Thumbnail') }}" value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="stock">{{ __('Product Stock') }}</label>
                                                <input type="text" class="input-field" id="stock" name="stock"
                                                    placeholder="{{ __('Product Stock') }}" value="" required>
                                            </div>


                                            <div class="form-group">
                                                <label for="measurement">{{ __('Product Measurement') }}</label>
                                                <input type="text" class="input-field" id="measurement"
                                                    name="measurement" placeholder="{{ __('Product Measurement') }}"
                                                    value="">
                                            </div>

                                            <div class="form-group">
                                                <label for="current_price">{{ __('Product Current Price') }}</label>
                                                <input type="text" class="input-field" id="current_price"
                                                    name="current_price" placeholder="{{ __('Product Current Price') }}"
                                                    value="" required>
                                            </div>


                                            <div class="form-group">
                                                <label for="previous_price">{{ __('Product Previous Price')
                                                    }}</label>
                                                <input type="text" class="input-field" id="previous_price"
                                                    name="previous_price"
                                                    placeholder="{{ __('Product Previous Price') }}" value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="youtube">{{ __('Youtube Video URL') }}</label>
                                                <input type="text" class="input-field" id="youtube" name="youtube"
                                                    placeholder="{{ __('Youtube Video URL') }}" value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="tags">{{ __('Tags') }}</label>
                                                <input type="text" class="input-field" id="tags" name="tags"
                                                    placeholder="{{ __('Tags') }}" value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="meta_tags">{{ __('Meta Tag') }}</label>
                                                <input type="text" class="input-field" id="meta_tags" name="meta_tag"
                                                    placeholder="{{ __('Meta Tag') }}" value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="meta_description">{{ __('Meta Description') }}</label>
                                                <input type="text" class="input-field" id="meta_description"
                                                    name="meta_description" placeholder="{{ __('Meta Description') }}"
                                                    value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="sizes">{{ __('Sizes') }}</label>
                                                <input type="text" class="input-field" id="sizes" name="sizes"
                                                    placeholder="{{ __('Sizes') }}" value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="size_quantity">{{ __('Size Quantity') }}</label>
                                                <input type="text" class="input-field" id="size_quantity"
                                                    name="size_quantity" placeholder="{{ __('Size Quantity') }}"
                                                    value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="size_extra_price">{{ __('Size Extra Price') }}</label>
                                                <input type="text" class="input-field" id="size_extra_price"
                                                    name="size_extra_price" placeholder="{{ __('Size Extra Price') }}"
                                                    value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="colors">{{ __('Colors') }}</label>
                                                <input type="text" class="input-field" id="colors" name="colors"
                                                    placeholder="{{ __('Colors') }}" value="">
                                            </div>


                                            <div class="form-group">
                                                <label for="affiliate_link">{{ __('Affiliate link') }}</label>
                                                <input type="text" class="input-field" id="affiliate_link"
                                                    name="affiliate_link" placeholder="{{ __('Affiliate link') }}"
                                                    value="" required>
                                            </div>


                                            <div class="form-group">
                                                <label for="">{{ __('Product Description') }}</label>
                                                <textarea name="product_description" class="input-field"
                                                    placeholder="{{__('Product Description')}}"></textarea>
                                            </div>


                                            <div class="form-group">
                                                <label for="">{{ __('Product Buy/Return Policy') }}</label>
                                                <textarea name="policy" class="input-field"
                                                    placeholder="{{ __('Product Buy/Return Policy') }}"></textarea>
                                            </div>



                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <div class="social-links-area">
                                                        <div class="row">
                                                            <div class="d-flex">
                                                                <label class="control-label mr-2" for="check_shedule">
                                                                    {{ __('Add Shedule')}}</label>
                                                                <label class="switch">
                                                                    <input type="checkbox" id="check_shedule"
                                                                        class="mx-2" name="check_shedule" value="1">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div id="show_shedule_module" class="d-none">
                                                        <div class="form-group">
                                                            <label for="shedule_date">{{ __('Shedule Date') }}</label>
                                                            <input type="date" class="input-field" id="shedule_date"
                                                                name="shedule_date"
                                                                placeholder="{{ __('Shedule Date') }}" value="">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="shedule_date">{{ __('Shedule Repeat') }}</label>
                                                            <select name="shedule_repeat" id="shedule_date"
                                                                class="input-field">
                                                                <option value="" selected disabled>{{ __('Select
                                                                    Repeat') }}</option>
                                                                <option value="daily">{{ __('Daily') }}</option>
                                                                <option value="weekly">{{ __('Weekly') }}</option>
                                                                <option value="monthly">{{ __('Monthly') }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit">@lang('Submit')</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js" type="text/javascript"></script>

<script>
    $( function() {
    $( ".dragable").draggable({ refreshPositions: true,opacity: 0.80,helper: "clone"});
    let array = [];
    $( ".form-group").droppable({
      drop: function( event, ui ) {
          array = [];
          if($(this).find('input,textarea').val()){
            array.push(($(this).find('input,textarea').val()));
          }
          array.push($(ui.draggable).attr('data-product'));
          if($(this).find('input,textarea').attr('name') == 'name[]' || $(this).find('input,textarea').attr('name') == 'description[]'){
            $(this).find('input,textarea').val(array).addClass(['bg-primary','text-white']);
          }else{
            $(this).find('input,textarea').val($(ui.draggable).attr('data-product')).addClass(['bg-primary','text-white']).attr('readonly',true);
          }
      }
    });
  } );


  $(document).on('change','#check_shedule',function(){
    
    
    if($(this).is(':checked')){
      $('#show_shedule_module').removeClass('d-none');
    }else{
        $('#show_shedule_module').addClass('d-none');
    }
        
    
  })



</script>
@endsection