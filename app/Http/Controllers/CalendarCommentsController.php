<?php

namespace App\Http\Controllers;

use App\Models\EditorialCalendar;
use App\Models\CalendarComment;
use App\Models\CalendarCommentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CalendarCommentsController extends Controller
{
    public function store(Request $request, EditorialCalendar $calendar)
    {
        $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // max 10MB
            'attachment_type' => 'nullable|in:file,link',
            'link_url' => 'required_if:attachment_type,link|url',
            'link_title' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Create comment
            $comment = CalendarComment::create([
                'content' => $request->content,
                'user_id' => auth()->id(),
                'calendar_id' => $calendar->id
            ]);

            // Handle file attachment
            if ($request->hasFile('attachment') && $request->attachment_type === 'file') {
                $file = $request->file('attachment');
                $path = $file->store('comments/attachments', 'public');
                
                CalendarCommentAttachment::create([
                    'comment_id' => $comment->id,
                    'type' => 'file',
                    'url' => Storage::url($path),
                    'filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]);
            }

            // Handle link attachment
            if ($request->attachment_type === 'link' && $request->link_url) {
                CalendarCommentAttachment::create([
                    'comment_id' => $comment->id,
                    'type' => 'link',
                    'url' => $request->link_url,
                    'title' => $request->link_title
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan',
                'comment' => $comment->load('user', 'attachments')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, CalendarComment $comment)
    {
        // Check if user owns the comment
        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengedit komentar ini'
            ], 403);
        }

        $request->validate([
            'content' => 'required|string'
        ]);

        try {
            $comment->update([
                'content' => $request->content
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diperbarui',
                'comment' => $comment->load('user', 'attachments')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(CalendarComment $comment)
    {
        // Check if user owns the comment
        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus komentar ini'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Delete file attachments
            foreach ($comment->attachments as $attachment) {
                if ($attachment->type === 'file') {
                    $path = str_replace('/storage/', '', $attachment->url);
                    Storage::disk('public')->delete($path);
                }
            }

            // Delete all attachments
            $comment->attachments()->delete();

            // Delete the comment
            $comment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus komentar: ' . $e->getMessage()
            ], 500);
        }
    }
} 