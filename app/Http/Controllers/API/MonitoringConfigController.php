<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MonitoringConfig;
use Illuminate\Http\Request;

class MonitoringConfigController extends Controller
{
    // Get all configs
    public function index()
    {
        return MonitoringConfig::all(); // Return all configs
    }

    // Store a new config
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'order' => 'required',
            'color' => 'required',
            'status' => 'required',
            'description' => 'required',
            'days' => 'required|integer',
            'condition' => 'required|in:<,>,=,<=,>=',
        ]);

        $config = MonitoringConfig::create($validatedData); // Create a new config

        return response()->json($config, 201); // Return the created config
    }

    // Show a specific config
    public function show($id)
    {
        return MonitoringConfig::findOrFail($id); // Return the specific config
    }

    // Update an existing config
    public function update(Request $request, $id)
    {
        $config = MonitoringConfig::findOrFail($id); // Find the existing config

        $validatedData = $request->validate([
            'order' => 'required',
            'color' => 'required',
            'status' => 'required',
            'description' => 'required',
            'days' => 'required|integer',
            'condition' => 'required|in:<,>,=,<=,>=',
        ]);

        $config->update($validatedData); // Update the config with validated data

        return response()->json($config); // Return the updated config
    }

    // Delete a config
    public function destroy($id)
    {
        $config = MonitoringConfig::findOrFail($id); // Find the config
        $config->delete(); // Delete the config

        return response()->json(null, 204); // Return no content
    }
}
