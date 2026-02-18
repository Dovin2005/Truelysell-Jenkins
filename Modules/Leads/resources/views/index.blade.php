@extends('leads::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('leads.name') !!}</p>
@endsection
