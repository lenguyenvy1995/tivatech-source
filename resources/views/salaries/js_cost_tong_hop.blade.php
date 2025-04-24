<script>
    // Hàm tính tổng các khoản chi phí
    function cost_tong_hop() {
        // Lấy giá trị từ các input
        let bhxh = parseFloat($('#bhxh').val()) || 0; // Giá trị bảo hiểm xã hội        
        let attendance_bonus = parseFloat($('#attendance_bonus').val()) || 0; // Thưởng chuyên cần
        let pc_rice = parseFloat($('#actual_work_days').val()) * 20000 || 0; // Phụ cấp cơm trưa
        let pc_phone = parseFloat($('#phone_allowance').val()) || 0; // Phụ cấp điện thoại
        let bonus = parseFloat($('#bonus').val()) || 0; // Thưởng
        let other_expenses = parseFloat($('#other_expenses').val()) || 0; // Chi phí khác

        // Tính tổng
        let total = bhxh + attendance_bonus + pc_rice + pc_phone + bonus + other_expenses;

        // Hiển thị kết quả
        $('#rs-tong-hop').text(new Intl.NumberFormat().format(total)); // Format theo định dạng số

        return total; // Trả về tổng để sử dụng trong các hàm khác
    }

    // Hàm tính tổng lương thực lãnh
    function total_salary() {
        // Lấy giá trị từ các input
        let real_salary = parseFloat($('#real_salary').val()) || 0; // Lương cơ bản thực tế    
        let kpi = parseFloat($('#kpi').val()) || 0; // Thưởng KPI
        let total_cost = cost_tong_hop(); // Tổng chi phí từ hàm cost_tong_hop

        // Tính tổng lương thực lãnh
        let total_salary = real_salary + kpi + total_cost;
        // Hiển thị kết quả total_salary
        $('#t_total_salary').val(total_salary); // Format theo định dạng số
        console.log(total_salary);
        
        $('#rs-total-salary').text(new Intl.NumberFormat('vi-VN').format(total_salary)); // Format theo định dạng số
    }

    // Danh sách các trường cần theo dõi
    const fields = [
        '#bhxh',
        '#attendance_bonus',
        '#actual_work_days',
        '#phone_allowance',
        '#bonus',
        '#other_expenses',
        '#real_salary',
        '#kpi',
    ];

    // Lắng nghe sự kiện trên tất cả các trường
    $(fields.join(',')).on('input change keyup', function() {
        // Đảm bảo hàm này chỉ chạy sau khi dữ liệu giao diện được cập nhật
        total_salary();
    });
</script>
