<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{

    /**
     * Get search roles
     *
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page',PHP_INT_MAX);

        $keyword = $request->input('keyword', '');

        $data = Role::with(['group'])->where(fn ($q) =>
                $q->where('name', 'like', '%'.$keyword.'%')
            ->orWhere('description', 'like', '%'.$keyword.'%')
        )->whereHas('group', fn($q) => $q->where('system_id', '=', 4))->orderBy('name')->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Get all role info
     *
     */
    public function show($id)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Role info',
            'data'    => Role::where('id', '=', $id)->first()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'min:2',
                Rule::unique('roles')->whereNull('deleted_at'),
            ]
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors();
            $first = $errors->getMessages();
            return response()->json([
                'status'  => false,
                'message' => (reset($first))[0],
                'errors'  => $errors
            ]);
        }

        DB::connection()->beginTransaction();

        try {

            $role = Role::create($request->all());

            DB::connection()->commit();

            return response()->json([
                'status' => true,
                'message' => 'Role saved successfully!',
                'data' => $role
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Role::find($id);

        if (!$data) {
            return response()->json(['messages' => 'No data found.'], 202);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'min:2',
                Rule::unique('roles')->ignore($id)->whereNull('deleted_at'),
            ]
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
                'status' => true,
                'message' => 'Role updated successfully!',
                'data' => $data
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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // if ( ! _userAccess('can.delete.roles')) {
        //     return response()->json([
        //         'status' => false,
        //         'message'    => 'Unauthorized.'
        //     ]);
        // }

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => $validator->errors()
            ]);
        }

        DB::connection()->beginTransaction();

        try {

            $role = Role::find($id);

            $role->delete();

            DB::connection()->commit();

            return response()->json([
                'status' => true,
                'message'    => '"'.$role->name.'" deleted successfully!',
                'data'   => $role
            ]);

        } catch (\Throwable $e) {

            DB::connection()->rollback();
            return response()->json([
                'status'  => false,
                'message' => 'Unable to process request. Please try again.',
                'data'    => $e->getMessage()
            ]);
        }
    }

}
