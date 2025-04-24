<?php

namespace App\Http\Controllers;

use App\Models\DesignWebsite;
use App\Models\Domain;
use App\Models\Hosting;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DesignWebsiteController extends Controller
{
    public function index()
    {
        $websites = DesignWebsite::with(['domain', 'hosting'])->latest()->get();
        return view('design_websites.index', compact('websites'));
    }
    public function getData(Request $request)
    {
        $data = DesignWebsite::with(['domain', 'hosting']);

        if (is_numeric($request->status)) {
            $data->where('design_website.status', $request->status);
        }

        return DataTables::of($data)
            ->addIndexColumn() // 👈 Thêm dòng này
            ->editColumn('DT_RowIndex', function ($row) {
                return '<div class="text-center">' . $row->DT_RowIndex . '</div>';
            })
            ->editColumn('prices', function ($row) {
                return '<div class="text-right">' . number_format((int) $row->prices, 0, ',', '.') . ' đ</div>';
            })
            ->addColumn('domain_column', function ($row) {
                $domain = $row->getRelation('domain');
                $domainName = isset($domain) && !empty($domain->domain)
                    ? $domain->domain
                    : '(chưa có)';
                $supplier = strip_tags($row->ownership);
                $regDate = $row->domain && is_object($row->domain) && $row->domain->registration_date
                    ? \Carbon\Carbon::parse($row->domain->registration_date)->format('d/m/Y')
                    : '---';
                $expDate = $row->domain && is_object($row->domain) && $row->domain->expiration_date
                    ? \Carbon\Carbon::parse($row->domain->expiration_date)->format('d/m/Y')
                    : '---';
                $capacity = isset($row->hosting) && is_object($row->hosting) && $row->hosting->capacity
                    ? $row->hosting->capacity
                    : '---';

                $note = e($row->note ?? '');
                $tooltip = "Nhà cung cấp: {$supplier}<br>Đăng ký: {$regDate}<br>Hết hạn: {$expDate}<br>Dung lượng: {$capacity}GB<br>Ghi chú: {$note}";

                return '<span class="text-primary" data-toggle="tooltip" data-placement="top" data-html="true" title="' . e($tooltip) . '" data-order="' . e($domainName) . '">' . e($domainName) . '</span>';
            })
            ->editColumn('expiration_date', function ($row) {
                return '<div class="text-center">' . optional($row->expiration_date)->format('d/m/Y') . '</div>';
            })
            ->addColumn('ownership', function ($row) {
                return $row->ownership; // gọi accessor
            })
            ->addColumn('status_name', function ($row) {
                return '<div class="text-center">' . match ((int)$row->status) {
                    1 => '<span class="badge badge-success">HOẠT ĐỘNG</span>',
                    2 => '<span class="badge badge-info">BACKUPED</span>',
                    3 => '<span class="badge badge-warning">TẠM NGƯNG</span>',
                    4 => '<span class="badge badge-danger">HẾT HẠN</span>',
                    default => '<span class="badge badge-secondary">Không xác định</span>',
                } . '</div>';
            })
            ->addColumn('status_control', function ($row) {
                $statuses = [
                    1 => 'HOẠT ĐỘNG',
                    2 => 'BACKUPED',
                    3 => 'TẠM NGƯNG',
                    4 => 'HẾT HẠN',
                ];

                $html = '<select class="status-select" data-id="' . $row->id . '" style="width: 130px; padding: 2px 6px; font-size: 13px;">';                foreach ($statuses as $value => $label) {
                    $selected = $row->status == $value ? 'selected' : '';
                    $html .= '<option value="' . $value . '" ' . $selected . '>' . $label . '</option>';
                }
                $html .= '</select>';

                return $html;
            })
            ->addColumn('action', function ($row) {
                return '<div class="text-center">
    <a href="' . route('design-websites.show', $row->id) . '" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
    <button class="btn btn-sm btn-info note-btn" data-toggle="tooltip" title="Ghi chú" data-id="' . $row->id . '" data-note="' . e($row->note ?? '') . '"><i class="fas fa-sticky-note"></i></button>
    <a href="' . route('design-websites.edit', $row->id) . '" class="btn btn-sm btn-success" data-toggle="tooltip" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
    <button class="btn btn-sm btn-danger delete-btn" data-toggle="tooltip" title="Xóa" data-id="' . $row->id . '"><i class="fas fa-trash-alt"></i></button>
</div>';
            })
            ->rawColumns(['DT_RowIndex','expiration_date', 'status_name', 'action', 'ownership', 'domain_column', 'status_control', 'note_column', 'prices']) // Cho phép HTML trong status/action
            ->make(true);
    }
    public function show(DesignWebsite $designWebsite)
    {
        $designWebsite->load(['domain', 'hosting']);
        return view('design_websites.show', compact('designWebsite'));
    }
    public function create()
    {
        $domains = Domain::all();
        $hostings = Hosting::all();
        $users = User::all();
        return view('design_websites.create', compact('domains', 'hostings', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username_customer' => 'required|string|max:255',
            'domain_name' => 'required|string|max:255',
            'prices' => 'nullable|string',
            'registration_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:registration_date',
            'customer_phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'sales_staff' => 'nullable|string|max:255',
            'domain_login_link' => 'nullable|string|max:255',
            'domain_account' => 'nullable|string|max:255',
            'domain_password' => 'nullable|string|max:255',
            'hosting_supplier' => 'nullable|string|max:255',
            'hosting_login_link' => 'nullable|string|max:255',
            'hosting_account' => 'nullable|string|max:255',
            'hosting_password' => 'nullable|string|max:255',
            'hosting_capacity' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        // 1. Tạo domain
        $domain = Domain::firstOrCreate(
            ['domain' => $request->domain_name],
            [
                'login_link' => $request->domain_login_link,
                'account' => $request->domain_account,
                'password' => $request->domain_password,
                'registration_date' => $request->registration_date,
                'expiration_date' => $request->expiration_date,
            ]
        );

        // 2. Tạo hosting
        $hosting = Hosting::create([
            'supplier' => $request->hosting_supplier,
            'login_link' => $request->hosting_login_link,
            'account' => $request->hosting_account,
            'password' => $request->hosting_password,
            'capacity' => $request->hosting_capacity,
        ]);

        // 3. Tạo design website
        DesignWebsite::create([
            'username_customer' => $request->username_customer,
            'domain_id' => $domain->id,
            'hosting_id' => $hosting->id,
            'prices' => is_numeric($request->prices) ? $request->prices : preg_replace('/[^\d]/', '', $request->prices),
            'registration_date' => $request->registration_date,
            'expiration_date' => $request->expiration_date,
            'customer_phone' => $request->customer_phone,
            'email' => $request->email,
            'sales_staff' => $request->sales_staff,
            'note' => $request->note,
            'user_id' => $request->user_id,
        ]);

        return redirect()->route('design-websites.index')->with('success', 'Thêm mới thành công!');
    }

    public function edit(DesignWebsite $designWebsite)
    {
        $domains = Domain::all();
        $hostings = Hosting::all();
        $users = User::all();
        return view('design_websites.edit', compact('designWebsite', 'domains', 'hostings', 'users'));
    }

    public function update(Request $request, DesignWebsite $designWebsite)
    {
        // Kiểm tra xem có thay đổi domain hay không
        $request->validate([
            'username_customer' => 'required|string|max:255',
            'domain_name' => 'required|string|max:255',
            'prices' => 'nullable|string',
            'registration_date' => 'required|date',
            'expiration_date' => 'required|date|after_or_equal:registration_date',
            'customer_phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'user_id' => 'required|exists:users,id',
            'domain_login_link' => 'nullable|string|max:255',
            'domain_account' => 'nullable|string|max:255',
            'domain_password' => 'nullable|string|max:255',
            'hosting_supplier' => 'nullable|string|max:255',
            'hosting_login_link' => 'nullable|string|max:255',
            'hosting_account' => 'nullable|string|max:255',
            'hosting_password' => 'nullable|string|max:255',
            'hosting_capacity' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        $domain = $designWebsite->domain;
        $domain->update([
            'domain' => $request->domain_name,
            'login_link' => $request->domain_login_link,
            'account' => $request->domain_account,
            'password' => $request->domain_password,
            'registration_date' => $request->registration_date,
            'expiration_date' => $request->expiration_date,
        ]);

        $hosting = $designWebsite->hosting;
        $hosting->update([
            'supplier' => $request->hosting_supplier,
            'login_link' => $request->hosting_login_link,
            'account' => $request->hosting_account,
            'password' => $request->hosting_password,
            'capacity' => $request->hosting_capacity,
        ]);

        $designWebsite->update([
            'username_customer' => $request->username_customer,
            'prices' => preg_replace('/[^\d]/', '', $request->prices),
            'registration_date' => $request->registration_date,
            'expiration_date' => $request->expiration_date,
            'customer_phone' => $request->customer_phone,
            'email' => $request->email,
            'user_id' => $request->user_id,
            'note' => $request->note,
        ]);

        return redirect()->route('design-websites.show', $designWebsite->id)->with('success', 'Cập nhật thành công!');
    }

    public function destroy(DesignWebsite $designWebsite)
    {
        $designWebsite->delete();
        return redirect()->route('design-websites.index')->with('success', 'Đã xóa thành công!');
    }

    public function updateStatus(Request $request, DesignWebsite $designWebsite)
    {
        $designWebsite->status = $request->status;
        $designWebsite->save();

        return response()->json(['message' => 'Cập nhật trạng thái thành công!']);
    }

    public function updateNote(Request $request, DesignWebsite $designWebsite)
    {
        $designWebsite->note = $request->note;
        $designWebsite->save();

        return response()->json(['message' => 'Đã lưu ghi chú!']);
    }
}
