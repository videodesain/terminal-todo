<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        try {
            // Validasi input
            $request->validate([
                'content' => 'required|string|max:1000'
            ]);
            
            // Log aktivitas
            \Log::info("[COMMENT] User " . Auth::id() . " membuat komentar untuk task ID: " . $task->id);
            \Log::debug("[COMMENT] Content: " . substr($request->content, 0, 100) . (strlen($request->content) > 100 ? '...' : ''));
            
            // Buat komentar
            $comment = $task->comments()->create([
                'user_id' => Auth::id(),
                'content' => $request->content,
                'parent_id' => $request->parent_id,
                'attachments' => $request->attachments,
                'mentions' => $request->mentions
            ]);
            
            // Load relasi user untuk respons
            $comment->load('user');
            
            // Bersihkan cache jika ada
            try {
                \Cache::forget('task_' . $task->id);
                \Cache::forget('tasks.all');
                \Cache::forget('tasks.collection');
            } catch (\Exception $e) {
                \Log::warning("[COMMENT] Error saat membersihkan cache: " . $e->getMessage());
            }
            
            // Respons sesuai tipe request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Komentar berhasil ditambahkan',
                    'comment' => $comment,
                    'redirect' => route('tasks.show', $task->id) . '?comment_added=1'
                ]);
            }
            
            return redirect()->route('tasks.show', $task->id)
                ->with('success', 'Komentar berhasil ditambahkan');
        } catch (\Exception $e) {
            \Log::error("[COMMENT] Error saat membuat komentar: " . $e->getMessage());
            \Log::error("[COMMENT] " . $e->getTraceAsString());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan komentar: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['content' => 'Gagal menambahkan komentar: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, TaskComment $comment)
    {
        try {
            $this->authorize('update', $comment);

            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            // Log aktivitas
            \Log::info("[COMMENT] User " . Auth::id() . " memperbarui komentar ID: " . $comment->id);
            
            $comment->update([
                'content' => $request->content,
                'attachments' => $request->attachments,
                'mentions' => $request->mentions
            ]);

            // Bersihkan cache jika ada
            try {
                \Cache::forget('task_' . $comment->task_id);
                \Cache::forget('tasks.all');
                \Cache::forget('tasks.collection');
            } catch (\Exception $e) {
                \Log::warning("[COMMENT] Error saat membersihkan cache: " . $e->getMessage());
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komentar berhasil diperbarui',
                    'comment' => $comment->fresh()->load('user')
                ]);
            }

            return back()->with('success', 'Komentar berhasil diperbarui');
        } catch (\Exception $e) {
            \Log::error("[COMMENT] Error saat memperbarui komentar: " . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui komentar: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['content' => 'Gagal memperbarui komentar: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, TaskComment $comment)
    {
        try {
            $this->authorize('delete', $comment);

            // Log aktivitas
            \Log::info("[COMMENT] User " . Auth::id() . " menghapus komentar ID: " . $comment->id);
            
            $taskId = $comment->task_id;
            $comment->delete();

            // Bersihkan cache jika ada
            try {
                \Cache::forget('task_' . $taskId);
                \Cache::forget('tasks.all');
                \Cache::forget('tasks.collection');
            } catch (\Exception $e) {
                \Log::warning("[COMMENT] Error saat membersihkan cache: " . $e->getMessage());
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komentar berhasil dihapus'
                ]);
            }

            return back()->with('success', 'Komentar berhasil dihapus');
        } catch (\Exception $e) {
            \Log::error("[COMMENT] Error saat menghapus komentar: " . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus komentar: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['message' => 'Gagal menghapus komentar: ' . $e->getMessage()]);
        }
    }
} 