@extends('adminlte::page')

@section('title', 'Tra cứu Quảng cáo ATC')

@section('content_header')
    <h1>Tra cứu quảng cáo qua Google Ads Transparency Center</h1>
@stop

@section('content')
    <form id="transparency-form">
        @csrf
        <div class="row">
            <div class="col-md-5">
                <label for="advertiser-input">Tên nhà quảng cáo</label>
                <input list="advertisers" id="advertiser-input" class="form-control" placeholder="Nhập tên nhà quảng cáo...">
                <input type="hidden" name="advertiser" id="advertiser-id">
                <datalist id="advertisers"></datalist>
            </div>
            <div class="col-md-4">
                <x-adminlte-select name="country" label="Quốc gia">
                    <option value="VN">Việt Nam</option>
                </x-adminlte-select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <x-adminlte-button label="Tìm kiếm" theme="primary" type="submit" class="btn-block"/>
            </div>
        </div>
    </form>

    <div id="loading-spinner" class="text-center my-4" style="display: none;">
        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        <div>Đang tải quảng cáo...</div>
    </div>

    <div id="results"></div>
@stop

@push('js')
<script>
    let advertiserMap = {};

    $('#advertiser-input').on('keyup', function () {
        let keyword = $(this).val();
        if (keyword.length < 2) return;
        console.log('Searching for:', keyword);
        $.get("{{ route('ads.transparency.suggest') }}", { q: keyword }, function (data) {
            let $datalist = $('#advertisers');
            advertiserMap = {};
            $datalist.empty();
            data.forEach(advertiser => {
                advertiserMap[advertiser.name] = advertiser.advertiser_id;
                $datalist.append(`<option value="${advertiser.name}">`);
                console.log(advertiser.name, advertiser.advertiser_id);
                
            });
        });
    });

    $('#advertiser-input').on('change', function () {
        let name = $(this).val();
        $('#advertiser-id').val(advertiserMap[name] ?? '');
    });

    $('#transparency-form').on('submit', function (e) {
        e.preventDefault();

        let advertiserId = $('#advertiser-id').val();
        if (!advertiserId) {
            alert('Vui lòng chọn nhà quảng cáo từ gợi ý.');
            return;
        }

        let formData = $(this).serialize();
        let $spinner = $('#loading-spinner');
        let $results = $('#results');

        $results.empty();
        $spinner.show();

        $.ajax({
            url: "{{ route('ads.transparency.ajax') }}",
            type: "POST",
            data: formData,
            success: function (response) {
                $spinner.hide();

                if (!response.ads || response.ads.length === 0) {
                    $results.html(`<x-adminlte-callout theme='warning' title='Không có quảng cáo nào'></x-adminlte-callout>`);
                    return;
                }

                let html = `<div class="row">`;
                response.ads.forEach(ad => {
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <strong>Nền tảng:</strong> ${ad.platforms?.[0] ?? '-'}<br>
                                    <strong>Bắt đầu:</strong> ${ad.start_date ?? '-'}<br>
                                    <strong>Tiêu đề:</strong> ${ad.headline ?? '-'}<br>
                                    ${ad.image_url ? `<div class="mt-2"><img src="${ad.image_url}" class="img-fluid"/></div>` : ''}
                                </div>
                            </div>
                        </div>`;
                });
                html += `</div>`;
                $results.html(html);
            },
            error: function (err) {
                $spinner.hide();
                $results.html(`<x-adminlte-callout theme='danger' title='Lỗi khi tìm kiếm quảng cáo'></x-adminlte-callout>`);
                console.error(err);
            }
        });
    });
</script>
@endpush