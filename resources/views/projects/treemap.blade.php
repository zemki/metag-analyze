@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $project->name }} - Treemap Visualization</h1>
        <p class="text-gray-600">{{ $project->description }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div id="treemap-container">
            <treemap 
                :project='@json($project)'
                :cases='@json($cases)'
                :entries='@json($entries)'
                :media='@json($media)'>
            </treemap>
        </div>
    </div>

    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">About This Visualization</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">View Modes</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• <strong>Project Overview:</strong> Hierarchical view from project → cases → entities</li>
                    <li>• <strong>Entity Type:</strong> Group data by {{ $project->use_entity ? ($project->entity_name ?: 'entity') : 'media' }} types</li>
                    <li>• <strong>Temporal:</strong> Organize data by time periods (months → days)</li>
                    <li>• <strong>Participant:</strong> View data grouped by participants/cases</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Metrics</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• <strong>Entry Count:</strong> Number of data entries</li>
                    <li>• <strong>Total Duration:</strong> Combined time span of entries</li>
                    <li>• <strong>Unique Participants:</strong> Number of distinct cases/participants</li>
                </ul>
            </div>
        </div>
        <p class="text-sm text-gray-500 mt-4">
            <strong>Tip:</strong> Click on any rectangle to drill down into more detailed data. Use the "Back" button to return to the previous level.
        </p>
    </div>

    <div class="mt-6 flex justify-between">
        <a href="{{ url($project->path()) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Project
        </a>
        
        <div class="space-x-2">
            <button onclick="exportChart('png')" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Export PNG
            </button>
            <button onclick="exportChart('pdf')" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export PDF
            </button>
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Page
            </button>
        </div>
    </div>
</div>

<!-- Add bottom padding -->
<div class="h-20"></div>

<script>
function exportChart(format) {
    // Use the globally accessible treemap component
    if (window.treemapComponent && window.treemapComponent.exportChart) {
        window.treemapComponent.exportChart(format);
    } else {
        // Fallback: try to find chart by looking for Highcharts instances
        if (window.Highcharts && window.Highcharts.charts) {
            const charts = window.Highcharts.charts.filter(chart => chart && chart.renderTo);
            if (charts.length > 0) {
                const chart = charts[charts.length - 1]; // Get the last chart (most likely our treemap)
                const filename = `{{ $project->name }}-treemap-${new Date().toISOString().split('T')[0]}`;
                
                chart.exportChart({
                    type: format === 'pdf' ? 'application/pdf' : 'image/png',
                    filename: filename,
                    sourceWidth: 1200,
                    sourceHeight: 800,
                    scale: 2
                });
            } else {
                alert('Chart not ready for export. Please wait for the visualization to load.');
            }
        } else {
            alert('Chart export functionality not available.');
        }
    }
}
</script>

<style>
@media print {
    .navbar, .mt-6, .bg-gray-50 {
        display: none !important;
    }
    .container {
        max-width: 100% !important;
    }
    
    /* Hide export buttons when printing */
    button[onclick*="exportChart"], button[onclick*="window.print"] {
        display: none !important;
    }
}

/* Responsive button layout */
@media (max-width: 768px) {
    .mt-6.flex.justify-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .space-x-2 {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .space-x-2 > * {
        margin-left: 0 !important;
        width: 100%;
    }
}
</style>
@endsection