@extends('advertisement::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('advertisement.name') !!}</p>
@endsection
