<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Các dòng ngôn ngữ xác thực
    |--------------------------------------------------------------------------
    |
    | Các dòng sau chứa các thông báo lỗi mặc định được sử dụng bởi
    | lớp xác thực. Một số quy tắc có nhiều phiên bản như quy tắc size.
    | Bạn có thể tùy chỉnh các thông báo này tại đây.
    |
    */

    'accepted' => ':attribute phải được chấp nhận.',
    'active_url' => ':attribute không phải là một URL hợp lệ.',
    'after' => ':attribute phải là một ngày sau :date.',
    'after_or_equal' => ':attribute phải là một ngày sau hoặc bằng :date.',
    'alpha' => ':attribute chỉ có thể chứa các chữ cái.',
    'alpha_dash' => ':attribute chỉ có thể chứa các chữ cái, số và dấu gạch ngang.',
    'alpha_num' => ':attribute chỉ có thể chứa các chữ cái và số.',
    'array' => ':attribute phải là một mảng.',
    'before' => ':attribute phải là một ngày trước :date.',
    'before_or_equal' => ':attribute phải là một ngày trước hoặc bằng :date.',
    'between' => [
        'numeric' => ':attribute phải nằm giữa :min và :max.',
        'file' => ':attribute phải có dung lượng từ :min đến :max kilobytes.',
        'string' => ':attribute phải có độ dài từ :min đến :max ký tự.',
        'array' => ':attribute phải có từ :min đến :max phần tử.',
    ],
    'boolean' => ':attribute phải là true hoặc false.',
    'confirmed' => ':attribute xác nhận không khớp.',
    'date' => ':attribute không phải là một ngày hợp lệ.',
    'date_equals' => ':attribute phải là một ngày bằng với :date.',
    'date_format' => ':attribute không khớp với định dạng :format.',
    'different' => ':attribute và :other phải khác nhau.',
    'digits' => ':attribute phải có :digits chữ số.',
    'digits_between' => ':attribute phải có từ :min đến :max chữ số.',
    'dimensions' => ':attribute có kích thước hình ảnh không hợp lệ.',
    'distinct' => ':attribute có giá trị trùng lặp.',
    'email' => ':attribute phải là một địa chỉ email hợp lệ.',
    'ends_with' => ':attribute phải kết thúc bằng một trong các giá trị sau: :values.',
    'exists' => 'Giá trị đã chọn cho :attribute không hợp lệ.',
    'file' => ':attribute phải là một tệp.',
    'filled' => ':attribute phải có giá trị.',
    'gt' => [
        'numeric' => ':attribute phải lớn hơn :value.',
        'file' => ':attribute phải lớn hơn :value kilobytes.',
        'string' => ':attribute phải nhiều hơn :value ký tự.',
        'array' => ':attribute phải có nhiều hơn :value phần tử.',
    ],
    'gte' => [
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
        'file' => ':attribute phải lớn hơn hoặc bằng :value kilobytes.',
        'string' => ':attribute phải có ít nhất :value ký tự.',
        'array' => ':attribute phải có ít nhất :value phần tử.',
    ],
    'image' => ':attribute phải là một hình ảnh.',
    'in' => 'Giá trị đã chọn cho :attribute không hợp lệ.',
    'in_array' => ':attribute không tồn tại trong :other.',
    'integer' => ':attribute phải là số nguyên.',
    'ip' => ':attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4' => ':attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json' => ':attribute phải là một chuỗi JSON hợp lệ.',
    'lt' => [
        'numeric' => ':attribute phải nhỏ hơn :value.',
        'file' => ':attribute phải nhỏ hơn :value kilobytes.',
        'string' => ':attribute phải ít hơn :value ký tự.',
        'array' => ':attribute phải có ít hơn :value phần tử.',
    ],
    'lte' => [
        'numeric' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        'file' => ':attribute phải nhỏ hơn hoặc bằng :value kilobytes.',
        'string' => ':attribute không được nhiều hơn :value ký tự.',
        'array' => ':attribute không được có nhiều hơn :value phần tử.',
    ],
    'max' => [
        'numeric' => ':attribute không được lớn hơn :max.',
        'file' => ':attribute không được lớn hơn :max kilobytes.',
        'string' => ':attribute không được nhiều hơn :max ký tự.',
        'array' => ':attribute không được có nhiều hơn :max phần tử.',
    ],
    'mimes' => ':attribute phải là một tệp loại: :values.',
    'mimetypes' => ':attribute phải là một tệp loại: :values.',
    'min' => [
        'numeric' => ':attribute phải ít nhất là :min.',
        'file' => ':attribute phải ít nhất là :min kilobytes.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
        'array' => ':attribute phải có ít nhất :min phần tử.',
    ],
    'not_in' => 'Giá trị đã chọn cho :attribute không hợp lệ.',
    'not_regex' => ':attribute có định dạng không hợp lệ.',
    'numeric' => ':attribute phải là một số.',
    'present' => ':attribute phải có mặt.',
    'regex' => ':attribute có định dạng không hợp lệ.',
    'required' => ':attribute là bắt buộc.',
    'required_if' => ':attribute là bắt buộc khi :other là :value.',
    'required_unless' => ':attribute là bắt buộc trừ khi :other nằm trong :values.',
    'required_with' => ':attribute là bắt buộc khi :values có mặt.',
    'required_with_all' => ':attribute là bắt buộc khi tất cả :values có mặt.',
    'required_without' => ':attribute là bắt buộc khi :values không có mặt.',
    'required_without_all' => ':attribute là bắt buộc khi không có :values nào có mặt.',
    'same' => ':attribute và :other phải khớp.',
    'size' => [
        'numeric' => ':attribute phải bằng :size.',
        'file' => ':attribute phải có dung lượng :size kilobytes.',
        'string' => ':attribute phải có độ dài :size ký tự.',
        'array' => ':attribute phải chứa :size phần tử.',
    ],
    'starts_with' => ':attribute phải bắt đầu bằng một trong các giá trị sau: :values.',
    'string' => ':attribute phải là một chuỗi.',
    'timezone' => ':attribute phải là một múi giờ hợp lệ.',
    'unique' => ':attribute đã được sử dụng.',
    'uploaded' => ':attribute tải lên thất bại.',
    'url' => ':attribute có định dạng không hợp lệ.',
    'uuid' => ':attribute phải là một UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Các dòng ngôn ngữ xác thực tùy chỉnh
    |--------------------------------------------------------------------------
    |
    | Tại đây bạn có thể chỉ định các thông báo xác thực tùy chỉnh cho các thuộc tính bằng cách sử dụng
    | quy ước "attribute.rule" để đặt tên cho các dòng. Điều này giúp bạn nhanh chóng
    | chỉ định một dòng ngôn ngữ tùy chỉnh cho một quy tắc thuộc tính cụ thể.
    |
    */

    'custom' => [
        'name' => [
            'required' => 'Vui lòng nhập :attribute.',
            'unique' => ':attribute đã tồn tại. Vui lòng chọn tên khác.',
        ],
        // Thêm các tùy chỉnh khác nếu cần
    ],

    /*
    |--------------------------------------------------------------------------
    | Thuộc tính xác thực tùy chỉnh
    |--------------------------------------------------------------------------
    |
    | Các dòng sau được sử dụng để thay thế thuộc tính chỗ trống bằng một thứ gì đó
    | thân thiện hơn với người đọc, chẳng hạn như "Địa chỉ Email" thay vì "email".
    | Điều này giúp chúng ta làm cho thông báo trở nên rõ ràng hơn.
    |
    */

    'attributes' => [
        'name' => 'Domain',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'current_password' => 'Mật khẩu hiện tại',
        'keywords' => 'Từ khóa',
        'top_position' => 'Vị trí top',
        'quote_domain_id' => 'Quote Domain',
        'region' => 'Khu vực chạy quảng cáo',
        'keyword_type' => 'Loại từ khóa',
        'campaign_type' => 'Hình thức chiến dịch',
        'start_date' => 'Ngày bắt đầu',
        'end_date' => 'Ngày kết thúc',
    ],

];
