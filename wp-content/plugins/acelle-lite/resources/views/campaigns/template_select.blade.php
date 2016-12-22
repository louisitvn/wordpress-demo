@extends('layouts.frontend')

@section('title', trans('messages.campaigns') . " - " . trans('messages.template'))
	
@section('page_script')
	<script type="text/javascript" src="{{ URL::asset('assets/js/plugins/forms/styling/uniform.min.js') }}"></script>        
    <script type="text/javascript" src="{{ URL::asset('tinymce/tinymce.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/interactions.min.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('assets/js/core/libraries/jquery_ui/touch.min.js') }}"></script>
        
    <script type="text/javascript" src="{{ URL::asset('js/editor.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/validate.js') }}"></script>
	<script type="text/javascript" src="{{ URL::asset('js/listing.js') }}"></script>	
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

				@include('campaigns._steps', ['current' => 3])
			</div>

@endsection

@section('content')
			
			@if (!empty($campaign->html))
				<div class="pull-right">
					<a href="{{ action('CampaignController@templatePreview', $campaign->uid) }}" class="btn btn-info bg-grey">
						<i class="icon-cross2"></i> {{ trans('messages.cancel') }}
					</a>				
				</div>
			@endif
			
            <h2 class="mt-0 text-semibold">{{ trans('messages.select_temmplate_for_email_content') }}</h2>
            
            <ul class="nav nav-tabs nav-tabs-top top-divided text-semibold">
                <li class="active">
                    <a href="#top-justified-divided-tab1" data-toggle="tab">
                        <i class="icon-plus2"></i> {{ trans('messages.build_new_template') }}
                    </a>
                </li>
                <li class="">
                    <a href="#top-justified-divided-tab2" data-toggle="tab">
                        <i class="icon-stack-text"></i> {{ trans('messages.existed_templates') }}
                    </a>
                </li>                       
            </ul>
            
            <div class="tab-content">
                
                <div class="tab-pane pt-10 active" id="top-justified-divided-tab1">
                    @foreach(Acelle\Model\Template::templateStyles() as $name => $style)
						<div class="col-xxs-12 col-xs-6 col-sm-3 col-md-2">
							<a href="{{ action('CampaignController@templateBuild', ['uid' => $campaign->uid, 'style' => $name]) }}">
								<div class="panel panel-flat panel-template-style">
									<div class="panel-body">
										<img src="{{ url('images/template_styles/'.$name.'.png') }}" />
										<h5 class="mb-0 text-center">{{ trans('messages.'.$name) }}</h5>
									</div>
								</div>
							</a>
						</div>
					@endforeach
                </div>

                <div class="tab-pane" id="top-justified-divided-tab2">
                    <form class="listing-form"
                        sort-url="{{ action('TemplateController@sort') }}"
                        data-url="{{ action('TemplateController@choosing', ['campaign_uid' => $campaign->uid]) }}"
                        per-page="{{ Acelle\Model\Template::$itemsPerPage }}"					
                    >				
                        <div class="row top-list-controls">
                            <div class="col-md-9">
                                @if (Acelle\Model\Template::getAll()->count() >= 0)					
                                    <div class="filter-box">													
                                        <span class="filter-group">
                                            <span class="title text-semibold text-muted">{{ trans('messages.sort_by') }}</span>
                                            <select class="select" name="sort-order">
                                                <option value="custom_order" class="active">{{ trans('messages.custom_order') }}</option>
                                                <option value="name">{{ trans('messages.name') }}</option>
                                                <option value="created_at">{{ trans('messages.created_at') }}</option>
                                            </select>										
                                            <button class="btn btn-xs sort-direction" rel="asc" data-popup="tooltip" title="{{ trans('messages.change_sort_direction') }}" type="button" class="btn btn-xs">
                                                <i class="icon-sort-amount-asc"></i>
                                            </button>
                                        </span>
                                        <span class="filter-group">
                                            <span class="title text-semibold text-muted">{{ trans('messages.from') }}</span>
                                            <select class="select" name="from">
                                                <option value="all">{{ trans('messages.all') }}</option>
                                                <option value="mine" selected='selected'>{{ trans('messages.my_templates') }}</option>
                                                <option value="gallery">{{ trans('messages.gallery') }}</option>
                                            </select>										
                                        </span>
                                        <span class="text-nowrap">
                                            <input name="search_keyword" class="form-control search" placeholder="{{ trans('messages.type_to_search') }}" />
                                            <i class="icon-search4 keyword_search_button"></i>
                                        </span>
                                    </div>                                    
                                @endif
                            </div>
                            <div class="col-md-3 text-right">
                                <a target="_blank" href="{{ action('TemplateController@upload') }}" type="button" class="btn bg-info-800">
                                    <i class="icon icon-upload"></i> {{ trans('messages.upload_template') }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="pml-table-container">
                            
                            
                            
                        </div>
                    </form>
                </div>
            </div>

@endsection
