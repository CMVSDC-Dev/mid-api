<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Build a query
        $query = User::query();

        // Capture the searchField and searchValue from the request
        if ($request->searchField && $request->searchValue)
            // Apply the search filter
            $query->where($request->searchField, 'like', '%' . $request->searchValue . '%');


        // Capture the sortBy and sortType frm the request
        if ($request->sortBy)
            $query->orderBy($request->sortBy, $request->sortType ?? 'ASC');

        // Paginate the results
        $data = $query->paginate($request->rowsPerPage ?? 10);

        // Return the paginated data as JSON
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user_id)
    {
        /**Find user by id */
        $user = User::find($user_id);

        if($user) { /**If record found, send response with record */
            return response()->json([
                'data'    => $user
            ], 200);
        }
        /**otherwise send response with no record found*/
        return response()->json(['status'  => false, 'messages' => 'No data found.'], 202);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        /**validate token */
        $token = Auth::attempt($request->only('username', 'password'));
        /** If invalid, response unauthorized */
        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }
        /**if valid fetch record */
        $user = Auth::user();
        /**response with record */
        return response()->json([
            'user' => $user,
            'token' => $token,
        ],200);
    }

    /**Saving record */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
        ]);

        if ($validator->fails()) { /**If validation failed, response with error messages */
            $errors = $validator->errors();
            $first = $errors->getMessages();
            return response()->json([
                'message' => (reset($first))[0],
                'errors'  => $errors
            ], 422);
        }

        /**If passed validation, begin the transaction */
        DB::connection()->beginTransaction();

        try {

            /**Create user */
            $user = User::create(
                array_merge(
                    $request->all(),
                    ['password' => $request->password ?? 'password']
                )
            );
            /**Save User role/s */
            $roles = [];
            foreach($request->roles as $role) {
                $roles[] = ["user_id"=>$user->id, "role_id"=>$role];
            }

            UserRole::insert($roles);

            /**Commit the transactions */
            DB::connection()->commit();

            /**Response with success message */
            return response()->json([
                'message' => 'Record Successfully added!',
            ], 201);

        } catch (\Throwable $e) {
            /**If something wrong happens, rollback the transactios */
            DB::connection()->rollback();
            /**Response with error message */
            return response()->json([
                'message' => 'Unable to process request. Please try again.',
                'data'    => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, int $id)
    {
        /**Find the user by id */
        $data = User::find($id);

        /**If no record found, response with error message */
        if (!$data) {
            return response()->json(['messages' => 'No data found.'], 422);
        }

        /**If has record, begin the transaction */
        DB::connection()->beginTransaction();

        try {

            /**fill the updated data */
            $data->fill($request->all());
            /**Save updated data */
            $data->save();
            /**apply the update */
            $data->touch();

            /**If has roles */
            if($request['roles']) {
                /**find Role in UserRole */
                $userRole = UserRole::where('user_id', $data->id);
                /**then delete the current record */
                $userRole->delete();

                /**put all submitted roles into container*/
                $roles = [];
                foreach($request->roles as $role) {
                    $roles[] = ["user_id"=>$data->id, "role_id"=>$role];
                }
                /**Insert roles */
                UserRole::insert($roles);
            }

            /**Commit all transactions */
            DB::connection()->commit();

            /**response with update success */
            return response()->json([
                'message' => 'Record Successfully updated!',
                'data' => $data,
            ], 201);

        } catch (\Throwable $e) {
            /**If something wrong happens, rollback all transactions */
            DB::connection()->rollback();
            /**response with error message */
            return response()->json([
                'message' => 'Unable to process request. Please try again.',
                'data'    => $e->getMessage()
            ], 422);
        }
    }
}
