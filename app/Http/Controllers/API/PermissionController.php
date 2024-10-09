<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {

        $perPage = $request->input('per_page',PHP_INT_MAX);

        $keyword = $request->input('keyword', '');

        $data = Permission::where(function ($query) use ($keyword) {
            $query->where('code', 'like', '%'.$keyword.'%')
            ->orWhere('name', 'like', '%'.$keyword.'%')
            ->orWhere('description', 'like', '%'.$keyword.'%');
        })->orderBy('name')->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** Validate the incoming request data */
        $validator = Validator::make($request->only(['code', 'name']), [
            'code' => [
                'required',
                'max:20',
                Rule::unique('permissions')->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                Rule::unique('permissions')->whereNull('deleted_at')
            ],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $first = $errors->getMessages();
            return response()->json([
                'message' => (reset($first))[0],
                'errors'  => $errors
            ], 422);
        }

        DB::connection()->beginTransaction();

        try {
            $permission = Permission::create([
                'code'          => $request->code,
                'name'          => strtoupper($request->name),
                'description'   => $request->description,
                'active'        => $request->active,
                'created_by_id' => $request->created_by_id,
            ]);

            DB::connection()->commit();

            return response()->json([
                'message' => 'Saved successfully!',
                'data'    => $permission
            ], 201);

        } catch (\Throwable $e) {

            DB::connection()->rollback();
            return response()->json([
                'message' => 'Unable to process request. Please try again.',
                'data'    => $e->getMessage()
            ], 422);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json([
            'message' => 'Permission info',
            'data'    => Permission::find($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /** Validate the incoming request data */
        $validator = Validator::make(array_merge($request->only(['code', 'name']), ['id' => $id]), [
            'id'   => 'required|exists:permissions,id',
            'code' => [
                'required',
                'max:20',
                Rule::unique('permissions')->ignore($id)->whereNull('deleted_at')
            ],
            'name' => [
                'required',
                Rule::unique('permissions')->ignore($id)->whereNull('deleted_at')
            ],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $first = $errors->getMessages();
            return response()->json([
                'message' => (reset($first))[0],
                'errors'  => $errors
            ], 422);
        }

        DB::connection()->beginTransaction();

        try {

            // update
            $permission = Permission::find($id);

            $permission->code       = $request->code;
            $permission->name       = strtoupper($request->name);

            if($request->description)
                $permission->description       = $request->description;

            $permission->save();

            DB::connection()->commit();

            return response()->json([
                'status'  => true,
                'message' => 'Updated successfully!',
                'data'    => $permission
            ], 201);

        } catch (\Throwable $e) {

            DB::connection()->rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Unable to process request. Please try again.',
                'data'    => $e->getMessage()
            ], 422);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $data = Permission::find($id);

        if(!$data) {
            return response()->json(['message' => 'Record not found!'],204);
        }

        $data->delete();

        DB::connection()->commit();

        return response()->json(['message' => 'Record Successfully deleted!']);
    }

}
