<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Platform;
use App\Models\Team;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Task;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request)
    {
        $startTime = microtime(true);
        $requestId = uniqid('task_', true);
        
        \Log::info("[TASK] Request index dimulai - ID: $requestId, IP: " . $request->ip());
        
        try {
            // Bersihkan cache
            \Cache::forget('tasks.all');
            \Cache::forget('tasks.collection');
            
            // Ambil data task dengan filter
            $query = Task::with(['category', 'platform', 'team', 'assignees', 'creator'])
                ->orderBy('created_at', 'desc');

            // Filter berdasarkan status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan arsip
            if ($request->has('archived') && $request->archived) {
                $query->archived();
            } else {
                $query->active();
            }

            $tasks = $query->get();
            
            // Format data untuk frontend
            $formattedTasks = $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'start_date' => $task->start_date?->format('Y-m-d\TH:i'),
                    'due_date' => $task->due_date?->format('Y-m-d\TH:i'),
                    'completed_at' => $task->completed_at?->format('Y-m-d\TH:i'),
                    'archived_at' => $task->archived_at?->format('Y-m-d\TH:i'),
                    'category' => $task->category ? [
                        'id' => $task->category->id,
                        'name' => $task->category->name,
                        'color' => $task->category->color
                    ] : null,
                    'platform' => $task->platform ? [
                        'id' => $task->platform->id,
                        'name' => $task->platform->name,
                        'icon' => $task->platform->icon
                    ] : null,
                    'team' => $task->team ? [
                        'id' => $task->team->id,
                        'name' => $task->team->name
                    ] : null,
                    'assignees' => $task->assignees->map(fn ($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar_url' => $user->avatar_url
                    ])->toArray(),
                    'creator' => $task->creator ? [
                        'id' => $task->creator->id,
                        'name' => $task->creator->name
                    ] : null,
                    'created_at' => $task->created_at->format('d M Y H:i')
                ];
            })->toArray();
            
            return Inertia::render('Tasks/Index', [
                'tasks' => $formattedTasks,
                'categories' => Category::where('type', 'task')->where('is_active', true)->get(),
                'highlight' => $request->query('highlight'),
                'statusFilter' => $request->query('status'),
                'showArchived' => $request->query('archived', false),
                'timestamp' => time(),
                'requestId' => $requestId
            ]);
        } catch (\Exception $e) {
            \Log::error("[TASK] Error saat memproses request index: " . $e->getMessage());
            return Inertia::render('Tasks/Index', [
                'tasks' => [],
                'categories' => Category::where('type', 'task')->where('is_active', true)->get(),
                'error' => "Gagal memuat data. Silakan refresh halaman."
            ]);
        }
    }

    public function create()
    {
        return Inertia::render('Tasks/Create', [
            'categories' => \App\Models\Category::active()->orderBy('name')->get(),
            'platforms' => \App\Models\Platform::orderBy('name')->get(),
            'teams' => \App\Models\Team::orderBy('name')->get(),
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'platform_id' => 'nullable|exists:platforms,id',
            'team_id' => 'nullable|exists:teams,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,in_progress,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'required|date',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id'
        ]);

        // Tambahkan created_by ke data task
        $validated['created_by'] = Auth::id();
        
        $task = Task::create($validated);
        $task->assignees()->attach($validated['assignees']);

        return redirect()->route('tasks.index')
            ->with('message', 'Task berhasil dibuat');
    }

    public function show($id)
    {
        // Pastikan selalu memuat relasi comments dan user
        $task = $this->taskService->getTaskById($id, ['category', 'platform', 'team', 'assignees', 'creator', 'comments.user']);

        // Log untuk debug
        \Log::debug("[TASK] Menampilkan detail task ID: $id, jumlah komentar: " . $task->comments->count());

        return Inertia::render('Tasks/Show', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'category' => [
                    'id' => $task->category->id,
                    'name' => $task->category->name,
                    'color' => $task->category->color
                ],
                'platform' => $task->platform ? [
                    'id' => $task->platform->id,
                    'name' => $task->platform->name,
                    'icon' => $task->platform->icon
                ] : null,
                'team' => $task->team ? [
                    'id' => $task->team->id,
                    'name' => $task->team->name
                ] : null,
                'priority' => $task->priority,
                'status' => $task->status,
                'start_date' => $task->start_date?->format('Y-m-d\TH:i:s'),
                'due_date' => $task->due_date?->format('Y-m-d\TH:i:s'),
                'completed_at' => $task->completed_at?->format('Y-m-d\TH:i:s'),
                'assignees' => $task->assignees->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar_url' => $user->avatar_url,
                    'role' => $user->pivot->role
                ]),
                'creator' => [
                    'id' => $task->creator->id,
                    'name' => $task->creator->name
                ],
                'comments' => $task->comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar_url' => $comment->user->avatar_url
                    ],
                    'created_at' => $comment->created_at->format('Y-m-d\TH:i:s')
                ]),
                'created_by' => $task->created_by, // Tambahkan created_by ID untuk pengecekan permission
                'created_at' => $task->created_at->format('Y-m-d\TH:i:s')
            ]
        ]);
    }

    public function edit(Task $task)
    {
        return Inertia::render('Tasks/Edit', [
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'category_id' => $task->category_id,
                'platform_id' => $task->platform_id,
                'team_id' => $task->team_id,
                'priority' => $task->priority,
                'status' => $task->status,
                'start_date' => $task->start_date?->format('Y-m-d\TH:i'),
                'due_date' => $task->due_date?->format('Y-m-d\TH:i'),
                'assignees' => $task->assignees->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name
                ])
            ],
            'categories' => \App\Models\Category::active()->orderBy('name')->get(),
            'platforms' => \App\Models\Platform::orderBy('name')->get(),
            'teams' => \App\Models\Team::orderBy('name')->get(),
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'platform_id' => 'nullable|exists:platforms,id',
            'team_id' => 'nullable|exists:teams,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:draft,in_progress,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'required|date',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id'
        ]);

        $task->update($validated);
        $task->assignees()->sync($validated['assignees']);

        return redirect()->route('tasks.index')
            ->with('message', 'Task berhasil diperbarui');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('message', 'Task berhasil dihapus');
    }

    /**
     * Update status task via PATCH request
     */
    public function updateStatus(Request $request, $id)
    {
        \Log::info("[TASK] Permintaan update status task ID: $id");
        
        try {
            // Validasi input
            $validated = $request->validate([
                'status' => 'required|in:draft,in_progress,completed,cancelled',
            ]);
            
            // Ambil task dari database
            $task = \App\Models\Task::with(['category', 'platform', 'team', 'assignees', 'creator'])->find($id);
            
            if (!$task) {
                \Log::warning("[TASK] Task dengan ID $id tidak ditemukan untuk update status");
                return response()->json([
                    'success' => false,
                    'message' => 'Task tidak ditemukan'
                ], 404);
            }
            
            // Verifikasi permission
            if (!auth()->user()->can('update', $task)) {
                \Log::warning("[TASK] User " . auth()->id() . " mencoba update status task $id tanpa permission");
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengubah status task ini'
                ], 403);
            }
            
            // Update status
            $oldStatus = $task->status;
            $task->status = $validated['status'];
            
            // Jika status berubah menjadi completed, set completed_at
            if ($task->status === 'completed' && $oldStatus !== 'completed') {
                $task->completed_at = now();
            } elseif ($task->status !== 'completed') {
                $task->completed_at = null;
            }
            
            // Simpan perubahan
            $task->save();
            
            // Bersihkan cache
            $this->cleanTaskCache();
            
            \Log::info("[TASK] Status task ID $id berhasil diubah dari $oldStatus menjadi {$task->status}");
            
            // Format response
            return response()->json([
                'success' => true,
                'message' => 'Status task berhasil diperbarui',
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'start_date' => $task->start_date?->format('Y-m-d\TH:i'),
                    'due_date' => $task->due_date?->format('Y-m-d\TH:i'),
                    'completed_at' => $task->completed_at ? $task->completed_at->format('Y-m-d\TH:i') : null,
                    'category' => $task->category ? [
                        'id' => $task->category->id,
                        'name' => $task->category->name,
                        'color' => $task->category->color
                    ] : null,
                    'platform' => $task->platform ? [
                        'id' => $task->platform->id,
                        'name' => $task->platform->name,
                        'icon' => $task->platform->icon
                    ] : null,
                    'team' => $task->team ? [
                        'id' => $task->team->id,
                        'name' => $task->team->name
                    ] : null,
                    'assignees' => $task->assignees->map(fn ($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar_url' => $user->avatar_url
                    ])->toArray(),
                    'creator' => $task->creator ? [
                        'id' => $task->creator->id,
                        'name' => $task->creator->name
                    ] : null,
                    'created_at' => $task->created_at->format('d M Y H:i')
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("[TASK] Error saat update status task ID $id: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper untuk membersihkan semua cache terkait task
     */
    private function cleanTaskCache()
    {
        try {
            // Hapus cache dengan query database
            \DB::table('cache')->where('key', 'LIKE', '%task%')->delete();
            
            // Clear cache dengan metode Cache facade
            \Cache::flush();
            
            \Log::debug("Task cache dibersihkan");
        } catch (\Exception $e) {
            \Log::error("Gagal membersihkan cache: " . $e->getMessage());
        }
    }

    // Tambahkan method baru untuk mengarsipkan task
    public function archive(Task $task)
    {
        try {
            $task->update(['archived_at' => now()]);
            
            return redirect()->route('tasks.index')
                ->with('message', 'Task berhasil diarsipkan');
        } catch (\Exception $e) {
            \Log::error("[TASK] Error saat mengarsipkan task: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengarsipkan task');
        }
    }

    // Tambahkan method untuk mengembalikan task dari arsip
    public function unarchive(Task $task)
    {
        try {
            $task->update(['archived_at' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Task berhasil dikembalikan dari arsip'
            ]);
        } catch (\Exception $e) {
            \Log::error("[TASK] Error saat mengembalikan task dari arsip: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengembalikan task dari arsip'
            ], 500);
        }
    }

    public function archived()
    {
        try {
            $requestId = uniqid('archived_');
            Log::info("[{$requestId}] Memulai request archived tasks");

            // Clear cache
            Cache::forget('tasks');

            // Get archived tasks
            $tasks = Task::with(['category', 'platform', 'team', 'assignees', 'creator'])
                ->whereNotNull('archived_at')
                ->orderBy('archived_at', 'desc')
                ->get();

            Log::info("[{$requestId}] Berhasil mengambil {$tasks->count()} task arsip");

            // Format tasks
            $formattedTasks = $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'category' => $task->category ? [
                        'id' => $task->category->id,
                        'name' => $task->category->name,
                        'color' => $task->category->color,
                    ] : null,
                    'platform' => $task->platform ? [
                        'id' => $task->platform->id,
                        'name' => $task->platform->name,
                    ] : null,
                    'team' => $task->team ? [
                        'id' => $task->team->id,
                        'name' => $task->team->name,
                    ] : null,
                    'assignees' => $task->assignees->map(function ($assignee) {
                        return [
                            'id' => $assignee->id,
                            'name' => $assignee->name,
                            'avatar_url' => $assignee->avatar_url,
                        ];
                    }),
                    'creator' => $task->creator ? [
                        'id' => $task->creator->id,
                        'name' => $task->creator->name,
                    ] : null,
                    'due_date' => $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : null,
                    'completed_at' => $task->completed_at ? $task->completed_at->format('Y-m-d\TH:i') : null,
                    'cancelled_at' => $task->cancelled_at ? $task->cancelled_at->format('Y-m-d\TH:i') : null,
                    'archived_at' => $task->archived_at ? $task->archived_at->format('Y-m-d\TH:i') : null,
                    'created_at' => $task->created_at->format('Y-m-d\TH:i'),
                    'updated_at' => $task->updated_at->format('Y-m-d\TH:i'),
                ];
            });

            Log::info("[{$requestId}] Berhasil memformat {$formattedTasks->count()} task");

            return Inertia::render('Tasks/Archived', [
                'tasks' => $formattedTasks,
                'categories' => Category::select('id', 'name', 'color')->get(),
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error("[{$requestId}] Error saat mengambil task arsip: " . $e->getMessage());
            return Inertia::render('Tasks/Archived', [
                'tasks' => [],
                'categories' => [],
                'error' => 'Gagal memuat data. Silakan refresh halaman.',
            ]);
        }
    }
} 