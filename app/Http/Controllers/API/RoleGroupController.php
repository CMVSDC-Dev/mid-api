<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\RoleGroup;
use DB;

class RoleGroupController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $perPage = $request->input('per_page',PHP_INT_MAX);
        $sortBy = $request->input('sortBy', '');
        $sortType = $request->input('sortType', '');
        $data = RoleGroup::where(function ($query) use ($keyword) {
            $keyword = str_replace(" ", "%", $keyword);
            $query->where('name', 'like', '%' . $keyword . '%');
                // ->orWhere('code', 'like', '%' . $keyword . '%');
        });

        if (!empty($sortBy) && !empty($sortType)) {
            $data = $data->orderBy($sortBy, $sortType);
        }

        $data = $data->paginate($perPage);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|unique:role_groups,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::connection()->beginTransaction();

        try {

            $data = RoleGroup::create($request->all());

            DB::connection()->commit();

            return response()->json([
                'message' => 'Record Successfully added!',
                'status' => 'success',
            ],201);

        } catch (\Throwable $e) {
            DB::connection()->rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Unable to process request. Please try again.',
                'data'    => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = RoleGroup::where('id', $id)->first();

        if (!$data) {
            return response()->json(['messages' => 'No data found.'], 404);
        }

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|unique:role_groups,name,'.$id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::connection()->beginTransaction();

        try {

            $data->fill($request->all());
            $data->save();

            $data->touch();

            DB::connection()->commit();

            return response()->json([
                'message' => 'Record Successfully updated!',
                'status' => 'success',
                'data' => $data,
            ],201);

        } catch (\Throwable $e) {
            DB::connection()->rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Unable to process request. Please try again.',
                'data'    => $e->getMessage()
            ]);
        }
    }

    public function destroy(int $id)
    {
        $data = RoleGroup::find($id);
        if(!$data)
        {
            return response()->json(['message' => "Record not found!"],204);
        }
        DB::connection()->beginTransaction();
        $data->delete();
        DB::connection()->commit();
        return response()->json(['message' => 'Record Successfully deleted!'],200);
    }
}
