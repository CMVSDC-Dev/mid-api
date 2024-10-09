<?php

namespace App\Http\Controllers\API\v1;

use Carbon\Carbon;
use App\Models\Entry;
use App\Models\EntryAudit;
use Illuminate\Http\Request;
use App\Helpers\GeneralHelpers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\EntryResource;
use App\Http\Resources\EntryCollection;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Build a query
        $query = Entry::query();

        // if request from API Admin App
        if ($request->isApi) {
            $query->whereNotNull('created_at');
        }

        // Capture the searchField and searchValue from the request
        if ($request->searchField && $request->searchValue){
            $value = $request->searchField !== 'Gender'
                ? '%' . $request->searchValue . '%'
                : $request->searchValue . '%';
            // Apply the search filter
            $query->where($request->searchField, 'like', $value);
        }

        // Capture the sortBy and sortType from the request
        if ($request->sortBy)
            $query->orderBy($request->sortBy, $request->sortType ?? 'ASC');

        // Paginate the results
        $data = $query->paginate($request->rowsPerPage ?? 10);

        // Return the paginated data as JSON
        return response()->json($data);
        //return new EntryCollection(Entry::limit(5)->get());
    }

    /**
     * @OA\Get(
     *     path="/api/entry/{id}",
     *     tags={"Entry"},
     *     summary="Get a single entry by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User details"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function show(Request $request, Entry $entry)
    {
        if ($entry)
            $this->createAuditTrail($request, $entry, 'View Details'); // mib entry audit trail

        return new EntryResource($entry);
    }

    /**
     * @OA\Post(
     *     path="/api/entry",
     *     tags={"Entry"},
     *     summary="Store Entry",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="Alias",
     *         in="query",
     *         description="Alias",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="BirthDate",
     *         in="query",
     *         description="Birth Date",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="YYYY-MM-DD")
     *     ),
     *     @OA\Parameter(
     *         name="FirstName",
     *         in="query",
     *         description="First Name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Gender",
     *         in="query",
     *         description="Gender",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="LastName",
     *         in="query",
     *         description="Last Name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="MaidenName",
     *         in="query",
     *         description="Maiden Name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="MiddleName",
     *         in="query",
     *         description="Middle Name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="Nationality",
     *         in="query",
     *         description="Nationality",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="Suffix",
     *         in="query",
     *         description="Suffix",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="Title",
     *         in="query",
     *         description="Title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="CompanyId",
     *         in="query",
     *         description="Company Id",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="DownloadStatus",
     *         in="query",
     *         description="Download Status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="IsShared",
     *         in="query",
     *         description="Is Shared",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="MemberId",
     *         in="query",
     *         description="Member Id",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="IsDeactivated",
     *         in="query",
     *         description="Is Deactivated",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="OtherName",
     *         in="query",
     *         description="Other Name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="BirthPlace",
     *         in="query",
     *         description="Birth Place",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PolicyNumber",
     *         in="query",
     *         description="Policy Number",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="ActionCodeId",
     *         in="query",
     *         description="Action Code Id",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="UnderwritingDate",
     *         in="query",
     *         description="Underwriting Date",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="MemberControlNumber",
     *         in="query",
     *         description="MemberControlNumber",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="ActionCode",
     *         in="query",
     *         description="Action Code",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Login successful"),
     *     @OA\Response(response="401", description="Invalid credentials")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Alias'         => 'max:255',
            'BirthDate'     => 'required|date',
            'FirstName'     => 'required|max:255',
            'Gender'        => 'required|max:10',
            'LastName'      => 'required',
            'MaidenName'    => 'max:255',
            'MiddleName'    => 'max:255',
            'Nationality'   => 'max:255',
            'Suffix'        => 'max:255',
            'Title'         => 'max:10',
            'CompanyId'     => 'integer',
            'DownloadStatus'=> 'max:255',
            'IsShared'      => 'boolean',
            'MemberId'      => 'integer',
            'IsDeactivated' => 'boolean',
            'OtherName'     => 'max:255',
            'BirthPlace'    => 'max:255',
            'PolicyNumber'  => 'required|max:255',
            'ActionCodeId'  => 'max:255',
            'UnderwritingDate' => 'date',
            'MemberControlNumber' => 'required|max:255',
            'ActionCode'    => 'max:255',
            'CompanyCode'   => 'max:255'
        ]);

        $validated['DownloadStatus'] = 0;
        $validated['IsShared'] = true; // make it default shared using API requests
        $validated['IsDeactivated'] = false; // make it default false using API requests

        $entry = Entry::create($validated);

        if ($entry)
            $this->createAuditTrail($request, $entry, 'Insert Entry'); // mib entry audit trail

        return new EntryResource($entry);
    }

    /**
     * Create Entry Inquiry Audit Trail
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Entry $entry
     * @param string $action (eg: Insert Entry|View Details|Update Details|Share|Share Request|Change Request)
     * @return void
     */
    public function createAuditTrail(Request $request, Entry $entry, string $action)
    {
        $helper = new GeneralHelpers();
        EntryAudit::create([
            'EntryID'       => $entry->Id ?? $entry->id,
            'Alias'         => $entry->Alias,
            'BirthDate'     => $entry->BirthDate,
            'BirthPlace'    => $entry->BirthPlace ?: "N/A",
            'CompanyId'     => $entry->CompanyId,
            'DownloadStatus'=> $entry->DownloadStatus,
            'FirstName'     => $entry->FirstName,
            'OtherName'     => $entry->OtherName,
            'Gender'        => $entry->Gender,
            'IsShared'      => $entry->IsShared,
            'IsDeactivated' => $entry->IsDeactivated,
            'LastName'      => $entry->LastName,
            'MaidenName'    => $entry->MaidenName,
            'MemberId'      => $entry->MemberId,
            'MiddleName'    => $entry->MiddleName,
            'Nationality'   => $entry->Nationality,
            'Suffix'        => $entry->Suffix,
            'Title'         => $entry->Title,
            'DateOfAction'  => Carbon::now(),
            'PersonInvolved'=> $helper->getAuthUser(),
            'ActionDone'    => $action,
            'PolicyNumber'  => $entry->PolicyNumber,
            'UnderwritingDate' => $entry->UnderwritingDate,
        ]);
    }

}
