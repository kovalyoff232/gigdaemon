@extends('layouts.app')

@section('content')
    {{-- Мы передаем ID счета из контроллера прямо в наш Vue-компонент --}}
    <invoice-detail-component :invoice-id="{{ $invoiceId }}"></invoice-detail-component>
@endsection