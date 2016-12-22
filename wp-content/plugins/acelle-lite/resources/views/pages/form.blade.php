@extends('layouts.page')

@section('title', trans('messages.' . $page->layout->alias))

@section('content')

                <form action="" method="POST" class="form-validate-jqueryz">
					{{ csrf_field() }}
                    
                    {!! $page->content !!}
                
                </form>
@endsection