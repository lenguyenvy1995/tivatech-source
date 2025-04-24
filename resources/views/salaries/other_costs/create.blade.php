<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="addForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Thêm Chi Phí Khác</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newKhachHang">Khách hàng</label>
                        <input type="text" id="newKhachHang" name="khach_hang" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="newDichVu">Dịch vụ</label>
                        <input type="text" id="newDichVu" name="dich_vu" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="newDoanhThu">Doanh thu</label>
                        <input type="number" id="newDoanhThu" name="doanh_thu" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="newHoaHong">Hoa hồng</label>
                        <input type="number" id="newHoaHong" name="hoa_hong" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>
