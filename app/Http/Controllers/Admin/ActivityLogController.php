<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('activity-log-table'))->only(['index']);
        $this->middleware($this->perm('activity-log-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $logs = AdminActivityLog::query()
            ->when($request->admin_id, fn ($q, $v) => $q->where('admin_id', $v))
            ->when($request->action,   fn ($q, $v) => $q->where('action', $v))
            ->when($request->module,   fn ($q, $v) => $q->where('module', 'like', "%{$v}%"))
            ->when($request->search,   fn ($q, $v) => $q->where('description', 'like', "%{$v}%"))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to,   fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $admins  = Admin::orderBy('name')->get(['id', 'name']);
        $actions = ['create', 'update', 'delete', 'login', 'logout'];

        return view('admin.activity-log.index', compact('logs', 'admins', 'actions'));
    }

    public function destroy(int $id)
    {
        AdminActivityLog::where('id', '<=', $id)->delete();
        return back()->with('success', 'تم حذف السجلات القديمة');
    }
}
