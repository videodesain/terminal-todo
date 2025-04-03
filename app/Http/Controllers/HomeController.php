<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\Category;
use App\Models\Platform;
use App\Models\MetricData;
use App\Models\EditorialCalendar;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Mengambil data untuk dashboard
        $userId = Auth::id();
        $now = Carbon::now();
        
        // Task stats
        $taskStats = [
            'total' => Task::count(),
            'draft' => Task::where('status', 'draft')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed' => Task::where('status', 'completed')->count(),
            'cancelled' => Task::where('status', 'cancelled')->count(),
        ];
        
        // My tasks - menggunakan created_by dan relasi assignees
        $myTasks = [
            'total' => Task::where('created_by', $userId)
                      ->orWhereHas('assignees', function($query) use ($userId) {
                          $query->where('users.id', $userId);
                      })
                      ->count(),
            'created' => Task::where('created_by', $userId)->count(),
            'assigned' => Task::whereHas('assignees', function($query) use ($userId) {
                          $query->where('users.id', $userId);
                      })->count(),
        ];
        
        // Editorial Calendar stats
        $calendarStats = [
            'total' => EditorialCalendar::count(),
            'upcoming' => EditorialCalendar::where('publish_date', '>=', $now)->count(),
            'monthly' => $this->getMonthlyCalendarStats(),
        ];
        
        // Team stats
        $userTeams = Team::with('users')
            ->whereHas('users', function($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->get()
            ->map(function($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                ];
            });
            
        $teamStats = [
            'total' => Team::count(),
            'userTeams' => $userTeams,
        ];
        
        // Recent tasks - menggunakan created_by dan relasi assignees
        $recentTasks = Task::with(['category', 'platform'])
            ->where(function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhereHas('assignees', function($q) use ($userId) {
                          $q->where('users.id', $userId);
                      });
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Upcoming events
        $upcomingEvents = EditorialCalendar::with(['category', 'platform'])
            ->where('publish_date', '>=', now())
            ->orderBy('publish_date', 'asc')
            ->limit(5)
            ->get();
        
        // Stats collection
        $stats = [
            'tasks' => $taskStats,
            'calendar' => $calendarStats,
            'teams' => $teamStats,
        ];
        
        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'myTasks' => $myTasks,
            'recentTasks' => $recentTasks,
            'upcomingEvents' => $upcomingEvents,
            'lastUpdated' => now()->format('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * Get monthly calendar stats
     */
    private function getMonthlyCalendarStats()
    {
        $months = [];
        $now = Carbon::now();
        $currentMonth = $now->month;
        
        // Get 3 months (current month and next 2)
        for ($i = 0; $i < 3; $i++) {
            $monthDate = $now->copy()->addMonths($i);
            $monthName = $monthDate->locale('id')->translatedFormat('F');
            $count = EditorialCalendar::whereMonth('publish_date', $monthDate->month)
                ->whereYear('publish_date', $monthDate->year)
                ->count();
                
            $months[] = [
                'name' => $monthName,
                'count' => $count,
                'status' => ($monthDate->month === $currentMonth) ? 'current' : 'upcoming',
            ];
        }
        
        return $months;
    }
} 