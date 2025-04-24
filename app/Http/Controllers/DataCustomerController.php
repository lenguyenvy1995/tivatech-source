<?php

namespace App\Http\Controllers;

use App\Models\DataCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DataCustomerController extends Controller
{
    /**
     * Hiển thị form chỉnh sửa khách hàng.
     */
    /**
     * Hiển thị form và danh sách khách hàng.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->has('id')) {
                // Trả về chi tiết khách hàng để chỉnh sửa
                $customer = DataCustomer::find($request->id);
                return response()->json($customer);
            }
            // Kiểm tra quyền admin
            if (Auth::user()->hasRole('saler')) {
                $query = DataCustomer::query();
                // Lọc trạng thái báo giá
                if ($request->has('quote_status') && $request->quote_status != '') {
                    $query->where('quote_status', $request->quote_status);
                }
                // Lọc ngày cập nhật
                if ($request->has('updated_at') && $request->updated_at != '') {
                    $query->whereDate('updated_at', $request->updated_at);
                }

                // Lọc từ khóa
                if ($request->has('keywords') && $request->keywords != '') {
                    $query->where('keywords', 'like', '%' . $request->keywords . '%');
                }
                if (Auth::user()->hasRole('admin')) {
                    $query->orderBy('updated_at', 'desc');

                }
                // Kiểm tra quyền saler
                else {
                    $query->where('user_id', Auth::id());
                }
                $query->orderBy('updated_at', 'desc');
            }
            // Trường hợp không có quyền phù hợp
            else {
                return response()->json(['error' => 'Bạn không có quyền xem dữ liệu'], 403);
            }

            // Trả về dữ liệu cho DataTables
            return DataTables::of($query)
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                })
                ->editColumn('updated_at', function ($row) {
                    return Carbon::parse($row->updated_at)->format('d-m-Y');
                })
                ->addColumn('actions', function ($row) {
                    return '<button class="btn btn-success btn-sm edit-btn" data-id="' . $row->id . '">Chỉnh sửa</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="' . $row->id . '">Xóa</button>';
                })
                ->editColumn('quote_status', function ($row) {
                    // Hiển thị trạng thái với nhãn màu sắc
                    switch ($row->quote_status) {
                        case 0:
                            return '<span class="badge bg-secondary">Chưa báo</span>';
                        case 1:
                            return '<span class="badge bg-primary">Đã báo</span>';
                        case 2:
                            return '<span class="badge bg-success">Đã chốt</span>';
                        default:
                            return '<span class="badge bg-danger">Không xác định</span>';
                    }
                })
                ->rawColumns(['actions', 'quote_status'])
                ->make(true);
        }

        return view('dataCustomers.index');
    }
    /**
     * Thêm mới hoặc cập nhật khách hàng.
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'keywords' => 'nullable|string',
            'feedback' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();

        if ($request->id) {
            // Chỉnh sửa khách hàng
            $customer = DataCustomer::findOrFail($request->id);
            $customer->update($data);
        } else {
            DataCustomer::updateOrCreate(
                [
                    'domain' => $data['domain'],
                    'user_id' => Auth::id()
                ], // Điều kiện tìm kiếm
                $data // Dữ liệu cập nhật hoặc thêm mới
            );
        }

        return response()->json(['success' => 'Dữ liệu đã được lưu thành công']);
    }
    public function edit($id)
    {
        $customer = DataCustomer::with('user')->findOrFail($id);
        return view('dataCustomers.edit', compact('customer'));
    }

    /**
     * Cập nhật thông tin khách hàng.
     */
    public function update(Request $request, $id)
    {

        $customer = DataCustomer::findOrFail($id);

        $request->validate([
            'domain' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'keywords' => 'nullable|string',
            'feedback' => 'nullable|string',
        ]);
        $customer->update($request->all());
        return redirect()->back()->with('success', 'Cập nhật thành công!');
    }
    public function destroy($id)
    {
        $customer = DataCustomer::findOrFail($id);
        $customer->delete();

        return response()->json(['success' => 'Dữ liệu đã được xóa thành công']);
    }
}
