
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Chỉnh sửa chi phí khác</h5>
               
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rowId">
                    <div class="mb-3">
                        <label for="khach_hang" class="form-label">Khách hàng</label>
                        <input type="text" class="form-control" id="khach_hang" name="khach_hang" required>
                    </div>
                    <div class="mb-3">
                        <label for="dich_vu" class="form-label">Dịch vụ</label>
                        <input type="text" class="form-control" id="dich_vu" name="dich_vu" required>
                    </div>
                    <div class="mb-3">
                        <label for="doanh_thu" class="form-label">Doanh thu</label>
                        <input type="number" class="form-control" id="doanh_thu" name="doanh_thu" required>
                    </div>
                    <div class="mb-3">
                        <label for="hoa_hong" class="form-label">Hoa hồng</label>
                        <input type="number" class="form-control" id="hoa_hong" name="hoa_hong" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>
