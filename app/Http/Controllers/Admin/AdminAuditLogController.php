<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    /**
     * Display searchable and filterable system-wide audit trail.
     */
    public function index(Request $request): View
    {
        $actionFilter = $request->query('action');
        $search = $request->query('q');

        $auditLogs = AuditLog::query()
            ->when($actionFilter && $actionFilter !== 'all', function ($q) use ($actionFilter) {
                $q->where('action', $actionFilter);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('action', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('auditable_type', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->with(['user', 'auditable'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('pages.admin.audit-logs.index', [
            'auditLogs' => $auditLogs,
            'actions' => $actions,
            'currentAction' => $actionFilter ?? 'all',
            'search' => $search,
        ]);
    }
}
