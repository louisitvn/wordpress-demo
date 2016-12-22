<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title')</title>
	
	@include('layouts._favicon')
	
	@include('layouts._head')
	
	@include('layouts._css')
	
	@include('layouts._js')
	
</head>

<body class="navbar-top color-scheme-{{ Auth::user()->getFrontendScheme() }}">

	<!-- Main navbar -->
	<div class="navbar navbar-{{ Auth::user()->getFrontendScheme() == "white" ? "default" : "inverse" }} navbar-fixed-top">
	
		@include('layouts._wordpress_top_bar')
	
		<div class="navbar-header">
			<a class="navbar-brand" href="{{ action('HomeController@index') }}">
				<img src="{{ URL::asset('assets/images/logo_' . (Auth::user()->getFrontendScheme() == "white" ? "dark" : "light") . '.png') }}" alt="">
			</a>

			<ul class="nav navbar-nav pull-right visible-xs-block">
				<li><a class="mobile-menu-button" data-toggle="collapse" data-target="#navbar-mobile"><i class="icon-menu7"></i></a></li>
			</ul>
		</div>

		<div class="navbar-collapse collapse" id="navbar-mobile">
		
			@include('layouts._wordpress_menu')

			<ul class="nav navbar-nav navbar-right">
		
				@include('layouts._top_activity_log')

			</ul>
		</div>
	</div>
	<!-- /main navbar -->
	
	<!-- Page header -->
	<div class="page-header">
		<div class="page-header-content">
			
			@yield('page_header')
			
		</div>
	</div>
	<!-- /page header -->

	<!-- Page container -->
	<div class="page-container">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">
			
				<!-- display flash message -->
				@include('common.errors')
				
				<!-- main inner content -->
				@yield('content')
                
			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->

		<!-- Footer -->
		<div class="footer text-muted">
			{!! trans('messages.copy_right') !!}
		</div>
		<!-- /footer -->

	</div>
	<!-- /page container -->
	
	@include("layouts._modals")
	
</body>
</html>
