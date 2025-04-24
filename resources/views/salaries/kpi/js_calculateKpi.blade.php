<script>
    function getKpiTableFromHtml() {
        const kpiTable = [];

        // Duyệt qua từng hàng trong bảng (trừ tiêu đề)
        $('#tbl_kpi_table tbody tr').each(function() {
            const doanhThu = parseInt($(this).find('td').eq(0).text().replace(/,/g, '')); // Cột "Doanh Thu"
            const hoaHong = parseFloat($(this).find('td').eq(1).text()); // Cột "Hoa Hồng"

            if (!isNaN(doanhThu) && !isNaN(hoaHong)) {
                kpiTable.push({
                    doanhThu,
                    hoaHong
                });
            }
        });

        // Thêm một mức Infinity để xử lý doanh thu lớn nhất
        kpiTable.push({
            doanhThu: Infinity,
            hoaHong: kpiTable[kpiTable.length - 1]?.hoaHong || 0
        });

        return kpiTable;
    }

    function calculateKpi(doanhSo, kpiTable) {
        let hoaHong = 0;
        let remainingDoanhSo = doanhSo;

        // Duyệt qua từng mốc trong bảng KPI
        for (let i = 0; i < kpiTable.length; i++) {
            const currentMoc = kpiTable[i];
            const nextMoc = kpiTable[i + 1] || {
                doanhThu: Infinity
            }; // Mốc tiếp theo hoặc vô cực

            // Tính phần doanh số trong khoảng hiện tại
            const range = Math.min(remainingDoanhSo, nextMoc.doanhThu - currentMoc.doanhThu);

            if (range > 0) {
                hoaHong += range * (currentMoc.hoaHong / 100);
                remainingDoanhSo -= range;
            }

            // Dừng khi không còn doanh số để tính
            if (remainingDoanhSo <= 0) break;
        }

        return hoaHong;
    }
    //nhấn doanh số thay đổi
    $('#doanhSo').on('keyup', function() {
        // Hiển thị bảng KPI
        calculateAndDisplayKpi().then(() => {
            total_salary();
        });

    });
    // Hàm tính và hiển thị KPI
    function calculateAndDisplayKpi() {
        if ($('#kpi_salary').is(':checked')) {
            var doanhSo = parseFloat($('#doanhSo').val());

            if (isNaN(doanhSo) || doanhSo <= 0) {
                toastr.warning('Vui lòng nhập doanh số hợp lệ!');
                return;
            }
            // Lấy bảng KPI từ HTML
            const kpiTable = getKpiTableFromHtml();
            // Tính KPI
            const kpi = parseInt(calculateKpi(doanhSo, kpiTable));
            // Hiển thị kết quả
            $('#kpi').val(kpi);
            $('#rs-doanhSo').text(parseInt(doanhSo).toLocaleString('vi-VN'))
            $('#rs-kpi').text(parseInt(kpi).toLocaleString('vi-VN'))
        } else {
            $('#doanhSo').val()
            $('#kpi').val(0);
            $('#rs-doanhSo').text(0)
            $('#rs-kpi').text(0)
        }
        return new Promise((resolve) => {
            resolve(); // Báo hoàn thành
        });
    }
</script>
