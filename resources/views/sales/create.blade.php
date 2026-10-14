@extends('layouts.app')
@section('title', 'Tambah Sales')
@section('header', 'Data Sales')
@section('content')
<x-ui.page-header title="Tambah Sales" subtitle="Informasi akun dan kontak sales perusahaan." />
@include('sales._form', ['record' => null])
@endsection
