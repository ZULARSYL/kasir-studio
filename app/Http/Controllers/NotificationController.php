<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Tampilkan halaman pusat notifikasi lengkap yang disesuaikan dengan role user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $notificationsData = $this->notificationService->getNotifications($user);

        $filter = $request->get('filter', 'all'); // all, unread, or category
        $search = strtolower(trim($request->get('q', '')));

        $items = collect($notificationsData['items']);

        if ($filter === 'unread') {
            $items = $items->where('is_read', false);
        } elseif ($filter !== 'all') {
            $items = $items->where('category', $filter);
        }

        if (! empty($search)) {
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['title']), $search) ||
                    str_contains(strtolower($item['message']), $search) ||
                    str_contains(strtolower($item['category']), $search);
            });
        }

        // Kategori yang ada untuk tab filter
        $categories = collect($notificationsData['items'])->pluck('category')->unique()->values();

        return view('notifikasi.index', [
            'user' => $user,
            'items' => $items,
            'unreadCount' => $notificationsData['unread_count'],
            'totalCount' => $notificationsData['total_count'],
            'categories' => $categories,
            'currentFilter' => $filter,
            'search' => $search,
        ]);
    }

    /**
     * API JSON untuk mengambil data notifikasi terbaru (bisa untuk polling AJAX).
     */
    public function getJson(Request $request)
    {
        $user = Auth::user();
        $data = $this->notificationService->getNotifications($user);

        return response()->json([
            'status' => 'success',
            'unread_count' => $data['unread_count'],
            'total_count' => $data['total_count'],
            'items' => $data['items'],
        ]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        $this->notificationService->markAllAsRead($user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Semua notifikasi telah ditandai sebagai dibaca.',
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Semua notifikasi berhasil ditandai sebagai dibaca.');
    }

    /**
     * Tandai satu notifikasi spesifik sebagai sudah dibaca.
     */
    public function markRead(Request $request, string $id)
    {
        $this->notificationService->markAsRead($id);

        if ($request->ajax() || $request->wantsJson()) {
            $user = Auth::user();
            $data = $this->notificationService->getNotifications($user);
            return response()->json([
                'status' => 'success',
                'unread_count' => $data['unread_count'],
            ]);
        }

        return redirect()->back();
    }
}
