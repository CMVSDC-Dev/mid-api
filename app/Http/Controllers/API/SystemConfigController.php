<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SystemConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function resetMonitoringData()
    {
        $resetTimestamp = Carbon::now();
        DB::table('system_config')->updateOrInsert(
            ['key' => 'monitoring_reset_timestamp'],
            ['value' => $resetTimestamp, 'description' => 'Monitoring Reset Timestamp']
        );

        return response()->json([
            'data' => $resetTimestamp,
            'message' => 'Monitoring date reset successfully'
        ]);
    }

}
