@extends('intercom::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('intercom.name') !!}</p>
@endsection
