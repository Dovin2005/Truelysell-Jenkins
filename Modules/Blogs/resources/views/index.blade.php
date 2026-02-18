@extends('blogs::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('blogs.name') !!}</p>
@endsection
