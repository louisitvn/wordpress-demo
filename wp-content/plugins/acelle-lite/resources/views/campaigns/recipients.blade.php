@extends('layouts.frontend')

@section('title', trans('messages.campaigns') . " - " . trans('messages.recipients'))
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
		
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
@endsection

@section('page_header')
	
			<div class="page-title">
				<ul class="breadcrumb breadcrumb-caret position-right">
					<li><a href="{{ action("HomeController@index") }}">{{ trans('messages.home') }}</a></li>
					<li><a href="{{ action("CampaignController@index") }}">{{ trans('messages.campaigns') }}</a></li>
				</ul>
				<h1>
					<span class="text-semibold"><i class="icon-paperplane"></i> {{ $campaign->name }}</span>
				</h1>

				@include('campaigns._steps', ['current' => 1])
			</div>

@endsection

@section('content')
                <form action="{{ action('CampaignController@recipients', $campaign->uid) }}" method="POST" class="form-validate-jqueryz">
					{{ csrf_field() }}
					
					<div class="row">
						<div class="col-md-6 list_select_box" target-box="segments-select-box" segments-url="{{ action('SegmentController@selectBox') }}">
							@include('helpers.form_control', ['include_blank' => trans('messages.choose'), 'type' => 'select', 'name' => 'mail_list_uid', 'label' => trans('messages.which_list_send'), 'value' => (is_object($campaign->mailList) ? $campaign->mailList->uid : ""), 'options' => Acelle\Model\MailList::getSelectOptions(Auth::user()), 'rules' => $rules])
						</div>
						<div class="col-md-6 segments-select-box">
							@if (is_object($campaign->mailList) && $campaign->mailList->segments()->count())
								@include('helpers.form_control', ['value' => (is_object($campaign->segment) ? $campaign->segment->uid : ""), 'include_blank' => trans('messages.choose'), 'type' => 'select', 'name' => 'segment_uid', 'label' => trans('messages.which_segment_send'), 'options' => $campaign->mailList->getSegmentSelectOptions()])
							@endif
						</div>
					</div>
					
					<hr>
					<div class="text-right">
						<button class="btn bg-teal-800">{{ trans('messages.next') }} <i class="icon-arrow-right7"></i> </button>
					</div>
					
				<form>
					
				
@endsection
