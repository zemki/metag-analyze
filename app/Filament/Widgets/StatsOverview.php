<?php

namespace App\Filament\Widgets;

use App\Cases;
use App\Entry;
use App\Mart\MartProject;
use App\Media;
use App\Project;
use App\Role;
use App\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // Cache stats for 5 minutes
        $cacheKey = 'dashboard_stats';
        $cacheDuration = 300; // 5 minutes

        return Cache::remember($cacheKey, $cacheDuration, function () {
            // Get user counts by role
            $roles = Role::withCount('users')->get();
            $usersByRole = $roles->map(function ($role) {
                return [
                    'name' => ucfirst($role->name),
                    'count' => $role->users_count,
                ];
            });

            // Get total users
            $totalUsers = User::count();

            // Build user roles description
            $userRolesDescription = $usersByRole->map(function ($role) {
                return "{$role['name']}: {$role['count']}";
            })->join(' | ');

            // Get MART projects count
            $martProjectsCount = MartProject::count();

            // Get total projects
            $totalProjects = Project::count();

            // Calculate non-MART projects
            $nonMartProjectsCount = $totalProjects - $martProjectsCount;

            // Get cases count
            $casesCount = Cases::count();

            // Get entries count
            $entriesCount = Entry::count();

            // Get media/files count
            $filesCount = Media::count();

            return $this->buildStats(
                $totalUsers,
                $userRolesDescription,
                $totalProjects,
                $martProjectsCount,
                $nonMartProjectsCount,
                $casesCount,
                $entriesCount,
                $filesCount
            );
        });
    }

    protected function buildStats(
        int $totalUsers,
        string $userRolesDescription,
        int $totalProjects,
        int $martProjectsCount,
        int $nonMartProjectsCount,
        int $casesCount,
        int $entriesCount,
        int $filesCount
    ): array {

        return [
            Stat::make('Total Users', $totalUsers)
                ->description($userRolesDescription)
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Projects', $totalProjects)
                ->description("MART: {$martProjectsCount} | Non-MART: {$nonMartProjectsCount}")
                ->descriptionIcon('heroicon-m-folder')
                ->color('primary'),

            Stat::make('Total Cases', $casesCount)
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Total Entries', $entriesCount)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Total Media Items', $filesCount)
                ->descriptionIcon('heroicon-m-paper-clip')
                ->color('gray'),
        ];
    }
}
