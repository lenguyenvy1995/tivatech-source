@extends('adminlte::page')

@section('title', 'Kết quả Quảng cáo')

@section('content_header')
    <h1>Kết quả Quảng cáo cho: "{{ $keyword }}"</h1>
@stop

@section('content')
    @if(count($ads))
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Tiêu đề</th>
                    <th>Liên kết</th>
                    <th>Mô tả</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ads as $ad)
                    <tr>
                        <td>{{ $ad['title'] ?? '-' }}</td>
                        <td>
                            <a href="{{ $ad['link'] }}" target="_blank">
                                {{ $ad['displayed_link'] ?? $ad['link'] }}
                            </a>
                        </td>
                        <td>{{ $ad['snippet'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <x-adminlte-callout theme="warning" title="Không tìm thấy quảng cáo nào">
            Vui lòng thử với từ khóa khác hoặc thay đổi khu vực.
        </x-adminlte-callout>
    @endif

    <a href="{{ route('ads.search') }}" class="btn btn-secondary mt-3">← Quay lại tìm kiếm</a>
@stop