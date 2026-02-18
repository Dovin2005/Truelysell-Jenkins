@extends('communication::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('communication.name') !!}</p>
@endsection
