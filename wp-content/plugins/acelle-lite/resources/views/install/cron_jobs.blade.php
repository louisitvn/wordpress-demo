@extends('layouts.install')

@section('title', trans('messages.cron_jobs'))

@section('page_script')    
    <script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('content')

	@include('elements._cron_jobs')
                            
    
	<br />
	<div class="text-right">                                    
		<a href="{{ action('InstallController@finish') }}" class="btn btn-primary bg-teal">{!! trans('messages.finish') !!} <i class="icon-arrow-right14 position-right"></i></a>
	</div>

@endsection
