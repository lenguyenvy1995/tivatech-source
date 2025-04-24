<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addModalLabel">Thêm chi phí</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newKhachHang">Khách hàng:</label>
                        <input type="text" class="form-control" id="newKhachHang" placeholder="Nhập tên khách hàng">
                    </div>
                    <div class="form-group">
                        <label for="newDichVu">Dịch vụ:</label>
                        <input type="text" class="form-control" id="newDichVu" placeholder="Nhập dịch vụ">
                    </div>
                    <div class="form-group">
                        <label for="newDoanhThu">Doanh thu:</label>
                        <input type="number" class="form-control" id="newDoanhThu" placeholder="Nhập doanh thu">
                    </div>
                    <div class="form-group">
                        <label for="newHoaHong">Hoa hồng:</label>
                        <input type="number" class="form-control" id="newHoaHong" placeholder="Nhập hoa hồng">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>
