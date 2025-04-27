<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class WebsiteController extends Controller
{
    // Hiển thị danh sách website
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'manager', 'techads'])) {
            $query = Website::with('users')->orderBy('name')->select('website.id', 'website.name');
        } elseif ($user->hasRole(['saler'])) {
            $query = $user->websites()->with('users')->orderBy('name')->select('website.id', 'website.name');
        } else {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập vào danh sách website.');
        }

        if ($request->ajax()) {
            return DataTables::of($query)

                ->addColumn('users', function ($website) {
                    return $website->users->pluck('fullname')->implode(', ');
                })
                ->addColumn('action', function ($website) {
                    $buttons = '';
                    if (Auth::user()->can('google ads')) {
                        $buttons .= '<a href="' . route('websites.edit', $website->id) . '" class="btn btn-warning btn-sm m-1"><i class="fas fa-edit"></i> Sửa</a>';
                        if (Auth::user()->hasRole('admin')) {
                            $buttons .= '<form action="' . route('websites.destroy', $website->id) . '" method="POST" style="display:inline-block;">';
                            $buttons .= csrf_field() . method_field('DELETE');
                            $buttons .= '<button type="submit" class="btn btn-danger btn-sm ml-1" onclick="return confirm(\'Bạn có chắc chắn muốn xóa website này?\');"><i class="fas fa-trash"></i> Xóa</button>';
                            $buttons .= '</form>';
                        }
                    }
                    $buttons .= '<a href="' . route('websites.campaigns', $website->id) . '" class="btn btn-info btn-sm m-1"><i class="fas fa-eye"></i> Xem Chiến Dịch</a>';
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('websites.index');
    }
    // Hiển thị form thêm mới website
    public function create()
    {
        return view('websites.create');
    }
    public function show()
    {
        return view('websites.create');
    }
    // Xử lý lưu website mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Tạo website mới
        $website = Website::create([
            'name' => $request->name
        ]);

        // Gắn website với user hiện tại
        $user = auth()->user();
        $user->websites()->attach($website->id);
        return response()->json(['success' => 'Website đã được thêm thành công.', 'website' => $website]);
    }


    // Hiển thị form chỉnh sửa website
    public function edit(Website $website)
    {
        return view('websites.edit', compact('website'));
    }

    // Cập nhật thông tin website
    public function update(Request $request, Website $website)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $website->update($request->only('name'));

        return redirect()->route('websites.index')->with('success', 'Website đã được cập nhật thành công.');
    }

    // Xóa website
    public function destroy(Website $website)
    {
        if (Auth::user()->hasRole('admin')) {  // Kiểm tra vai trò admin
            $website->delete();
            return redirect()->back()->with('success', 'Website đã được xóa thành công.');
        }
        return redirect()->back()->with('error', 'Bạn không có quyền xóa website này.');
    }

    // Hiển thị các chiến dịch liên kết với website
    public function showCampaigns(Website $website)
    {
        $campaigns = $website->campaigns; // Giả sử model Website có quan hệ với Campaign
        return view('websites.campaigns', compact('website', 'campaigns'));
    }
    // kiểm tra website đã ngừng chạy (in-active)
    public function inactiveCampaigns(Request $request)
    {
        $numberMonth = Carbon::now()->subMonths($request->numberMonth ?? 3);

        // Lấy các website có chiến dịch mới nhất đã kết thúc cách đây hơn $numberMonth
        $domains = Website::with(['latestCampaign', 'user'])
            ->whereHas('latestCampaign', function ($query) use ($numberMonth) {
                $query->where('end', '<=', $numberMonth);
            })
            ->withCount(['campaigns as latest_end' => function ($query) {
                $query->select(\DB::raw('MAX(end)'));
            }])
            ->orderByDesc('latest_end')
            ->limit(1000)
            ->get();

        if ($request->ajax()) {
            return DataTables::of($domains)
                ->addIndexColumn()
                ->editColumn('latestCampaign.end', function($row) {
                    return $row->latestCampaign ? Carbon::parse($row->latestCampaign->end)->format('d-m-Y') : '';
                })
                ->editColumn('user.fullname', function($row) {
                    return $row->user->fullname ?? '';
                })
                ->make(true);
        }

        return view('websites.inactive_campaigns');
    }
    /**
     * Xử lý kiểm tra  domain.
     */
    public function check(Request $request)
    {
        $domain = $request->input('domain');
        if (!$domain) {
            return response()->json(['status' => 'error', 'message' => 'Domain không hợp lệ.']);
        }

        $website = Website::where('name', 'LIKE', "%$domain%")->first();
        if ($website) {
            $campaign = $website->latestCampaign;

            if ($campaign) {
                return response()->json([
                    'status' => 'success',
                    'website' => $website->name,
                    'end' => Carbon::parse($campaign->end)->format('d-m-Y'),
                    'saler'=>$campaign->user->fullname
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Không tìm thấy chiến dịch cho website này.']);
            }
        }

        return response()->json(['status' => 'error', 'website' => $domain]);
    }
}
