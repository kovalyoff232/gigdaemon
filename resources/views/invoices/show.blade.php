@extends('layouts.app')

@section('content')
    <invoice-detail-component :invoice-id="{{ $invoiceId }}"></invoice-detail-component>
@endsection