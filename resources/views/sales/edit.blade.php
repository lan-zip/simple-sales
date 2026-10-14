@extends('layouts.app')
@section('title', 'Ubah Sales')
@section('header', 'Data Sales')
@section('content')
<x-ui.page-header title="Ubah Sales" subtitle="Informasi akun dan kontak sales perusahaan." />
@include('sales._form', ['record' => $sales])
@endsection
