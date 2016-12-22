@extends('layouts.frontend')

@section('title', $list->name . ": " . trans('messages.create_subscriber'))
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	
			@include("lists._header")

@endsection

@section('content')
				@include("lists._menu")
				
				<h2 class="text-semibold text-teal-800"><i class="icon-pencil"></i> {{ trans('messages.edit_subscriber') }}</h2>
				
                <form action="{{ action('SubscriberController@update', ['list_uid' => $list->uid, "uid" => $subscriber->uid]) }}" method="POST" class="form-validate-jqueryz">
					{{ csrf_field() }}
                    <input type="hidden" name="_method" value="PATCH">
					
					@include("subscribers._form")					
					
					<div class="text-right col-md-6">
						<button class="btn bg-teal mr-10"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
						<a href="{{ action('SubscriberController@index', $list->uid) }}" class="btn bg-grey-800"><i class="icon-cross2"></i> {{ trans('messages.cancel') }}</a>
					</div>
					
				<form>
					
@endsection
