<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\FCMController;
use App\Models\Announcement;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('announcement-table'))->only(['index', 'show']);
        $this->middleware($this->perm('announcement-add'))->only(['create', 'store']);
        $this->middleware($this->perm('announcement-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('announcement-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $announcements = Announcement::with(['schoolClass', 'classes'])
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->filled('class_id'), function ($q) use ($request) {
                if ($request->class_id === '0') {
                    // "general" = targets everyone (no class selected)
                    return $q->whereNull('class_id')->whereDoesntHave('classes');
                }

                return $q->where(fn ($w) => $w
                    ->where('class_id', $request->class_id)
                    ->orWhereHas('classes', fn ($c) => $c->where('classes.id', $request->class_id))
                );
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        return view('admin.announcements.index', compact('announcements', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        return view('admin.announcements.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'class_ids'    => 'nullable|array',
            'class_ids.*'  => 'exists:classes,id',
            'is_active'    => 'boolean',
            'published_at' => 'nullable|date',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $classIds = array_values(array_unique(array_map('intval', $data['class_ids'] ?? [])));
        unset($data['class_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = uploadImage('assets/uploads/announcements', $request->file('image'));
        }

        $data['class_id']     = $classIds[0] ?? null;
        $data['is_active']    = $request->boolean('is_active', true);
        $data['published_at'] = $data['published_at'] ?? now();

        $announcement = Announcement::create($data);
        $announcement->classes()->sync($classIds);

        if ($announcement->is_active) {
            // A student belongs to exactly one class, so one push per selected
            // class never notifies anyone twice. No classes selected = everyone.
            $targets = $classIds ?: [null];
            foreach ($targets as $target) {
                FCMController::sendToStudents($announcement->title, $announcement->body, $target, 'announcements');
            }
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'تم إضافة الإعلان وإرسال الإشعار بنجاح.');
    }

    public function edit(Announcement $announcement)
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        return view('admin.announcements.edit', compact('announcement', 'classes'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'class_ids'    => 'nullable|array',
            'class_ids.*'  => 'exists:classes,id',
            'is_active'    => 'boolean',
            'published_at' => 'nullable|date',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $classIds = array_values(array_unique(array_map('intval', $data['class_ids'] ?? [])));
        unset($data['class_ids']);

        if ($request->hasFile('image')) {
            $data['image'] = uploadImage('assets/uploads/announcements', $request->file('image'));
        }

        $data['class_id']  = $classIds[0] ?? null;
        $data['is_active'] = $request->boolean('is_active');

        $announcement->update($data);
        $announcement->classes()->sync($classIds);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'تم تحديث الإعلان بنجاح.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'تم حذف الإعلان.');
    }
}
