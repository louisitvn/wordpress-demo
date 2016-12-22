    <script>
		var DATATABLE_TRANSLATE_URL = '{{ action('Controller@datatable_locale') }}';
		var JVALIDATE_TRANSLATE_URL = '{{ action('Controller@jquery_validate_locale') }}';
		var APP_URL = '{{ url('/') }}';
		var LANG_OK = '{{ trans('messages.ok') }}';
		var LANG_DELETE_VALIDATE = '{{ trans('messages.delete_validate') }}';
		var LANG_DATE_FORMAT = '{{ trans('messages.j_date_format') }}';
	</script>