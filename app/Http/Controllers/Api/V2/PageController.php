<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\MartPage;
use App\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Get pages for a project
     */
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $pages = $project->pages()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
            'message' => 'Pages retrieved successfully',
        ]);
    }

    /**
     * Store a new page
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'show_on_first_app_start' => 'boolean',
            'button_text' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
        ]);

        $attributes['project_id'] = $project->id;

        // If no sort_order provided, set to last
        if (! isset($attributes['sort_order'])) {
            $attributes['sort_order'] = $project->pages()->count();
        }

        $page = MartPage::create($attributes);

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page created successfully',
        ], 201);
    }

    /**
     * Show a specific page
     */
    public function show(Project $project, Page $page): JsonResponse
    {
        $this->authorize('view', $project);

        // Ensure page belongs to project
        if ($page->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for this project',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page retrieved successfully',
        ]);
    }

    /**
     * Update a page
     */
    public function update(Request $request, Project $project, Page $page): JsonResponse
    {
        $this->authorize('update', $project);

        // Ensure page belongs to project
        if ($page->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for this project',
            ], 404);
        }

        $attributes = $request->validate([
            'name' => 'string|max:255',
            'content' => 'string',
            'show_on_first_app_start' => 'boolean',
            'button_text' => 'string|max:255',
            'sort_order' => 'integer|min:0',
        ]);

        $page->update($attributes);

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page updated successfully',
        ]);
    }

    /**
     * Delete a page
     */
    public function destroy(Project $project, Page $page): JsonResponse
    {
        $this->authorize('update', $project);

        // Ensure page belongs to project
        if ($page->project_id !== $project->id) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for this project',
            ], 404);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully',
        ]);
    }

    /**
     * Bulk update page order
     */
    public function updateOrder(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $request->validate([
            'pages' => 'required|array',
            'pages.*.id' => 'required|integer|exists:pages,id',
            'pages.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->pages as $pageData) {
            $page = MartPage::find($pageData['id']);

            // Ensure page belongs to project
            if ($page && $page->project_id === $project->id) {
                $page->update(['sort_order' => $pageData['sort_order']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Page order updated successfully',
        ]);
    }
}
