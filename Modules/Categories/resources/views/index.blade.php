@extends('categories::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('categories.name') !!}</p>
@endsection
