@extends('layouts.page')

@section('title', "")
	


@section('content')
  <div class="row">
    <div class="col-md-12 tex-center">
      <script src='https://www.google.com/recaptcha/api.js'></script>
      <form action="{{ action('MailListController@embeddedFormSubscribe', $list->uid) }}" method="POST" class="form-validate-jqueryz">
        @foreach (request()->all() as $key => $value)
          <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
        @endforeach
        <div style="margin: 100px auto; width: 300px;text-align:center">
          <div class="g-recaptcha" data-sitekey="6LfyISoTAAAAABJV8zycUZNLgd0sj-sBFjctzXKw"></div>
          <br />
          <input type="submit" class="btn btn-primary" value="{{ trans('messages.confirm') }}" />
        </div>
      </form>
    </div>
  </div>

@endsection