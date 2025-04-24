@extends('adminlte::page')

@section('title', 'Domains with Inactive Campaigns')

@section('content_header')
    <h1>Website Ngừng Hoạt Động</h1>
@stop

@section('content')
    <table class="table table-success table-striped table-bordered table-hover">
        <thead class="text-center">
            <tr>
                <th>STT</th>
                <th>Domain</th>
                <th>Kết Thúc</th>
                <th>Nhân Viên Sales</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($domains as $key=> $domain)
                <tr>
                    <td class="text-center">{{$loop->iteration}}</td>
                    <td>{{ $domain->name }}</td>
                    <td class="text-center">{{ Carbon\Carbon::parse($domain->latestCampaign->end)->format('d-m-Y') }}</td>
                    <td>{{ $domain->user->fullname }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
