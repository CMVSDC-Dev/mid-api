<?php

namespace App\Http\Controllers\API\v1;

use Carbon\Carbon;
use App\Models\Entry;
use App\Models\Company;
use App\Models\EntryAudit;
use Illuminate\Http\Request;
use App\Helpers\PaginationHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class InquiryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/search",
     *     tags={"Inquiry"},
     *     summary="Inquiry",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="FirstName",
     *         in="query",
     *         description="First Name",
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
     *         name="MiddleName",
     *         in="query",
     *         description="Middle Name",
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
     *         name="Alias",
     *         in="query",
     *         description="Alias",
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
     *         name="CompanyCode",
     *         in="query",
     *         description="Company Code",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="WildSearch",
     *         in="query",
     *         description="WildSearch",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Successful"),
     *     @OA\Response(response="401", description="Invalid credentials")
     * )
     */
    public function search(Request $request)
    {
        $LastName = $request->LastName;
        $FirstName = $request->FirstName;
        $MiddleName = $request->MiddleName;
        $Alias = $request->Alias;
        $MaidenName = $request->MaidenName;
        $BirthDate = $request->BirthDate ? date('Y-m-d H:i:s', strtotime($request->BirthDate)) : '';
        $Gender = $request->Gender;
        $CompanyCode = $request->CompanyCode;
        $CompanyId = $request->CompanyId;
        $WildSearch = $request->WildSearch;

        $query = Entry::join('MibImpairments', 'MibEntries.Id', '=', 'MibImpairments.MibEntryId');

        // Add where clauses only if the request inputs are not empty
        if (!empty($LastName)) {
            $query->where('MibEntries.LastName', 'like', '%' . $LastName . '%');
        }
        if (!empty($FirstName)) {
            $query->where('MibEntries.FirstName', 'like', '%' . $FirstName . '%');
        }
        if (!empty($MiddleName)) {
            $query->where('MibEntries.MiddleName', 'like', '%' . $MiddleName . '%');
        }
        if (!empty($Alias)) {
            $query->where('MibEntries.Alias', 'like', '%' . $Alias . '%');
        }
        if (!empty($MaidenName)) {
            $query->where('MibEntries.MaidenName', 'like', '%' . $MaidenName . '%');
        }
        if (!empty($BirthDate)) {
            $query->whereDate('MibEntries.BirthDate', $BirthDate);
        }
        if (!empty($Gender)) {
            $query->where('MibEntries.Gender', 'like', $Gender . '%');
        }
        if (!empty($CompanyCode)) {
            $query->where('MibEntries.CompanyCode', 'like', '%' . $CompanyCode . '%');
        }
        if (!empty($CompanyId)) {
            $query->where('MibEntries.CompanyId', $CompanyId);
        }

        if(!empty($WildSearch)){
            $query->where('MibEntries.FirstName', 'like', '%' . $WildSearch . '%');
            $query->orWhere('MibEntries.LastName', 'like', '%' . $WildSearch . '%');
            $query->orWhere('MibEntries.MiddleName', 'like', '%' . $WildSearch . '%');
            $query->orWhere('MibEntries.MaidenName', 'like', '%' . $WildSearch . '%');
            $query->orWhere('MibEntries.Alias', 'like', '%' . $WildSearch . '%');
            $query->orWhere('MibEntries.Gender', 'like', '%' . $WildSearch . '%');
            $query->orWhere('MibEntries.CompanyCode', 'like', '%' . $WildSearch . '%');
        }

        $query->select(
            'MibEntries.Id',
            'MibEntries.Alias',
            'MibEntries.BirthDate',
            'MibEntries.FirstName',
            'MibEntries.Gender',
            'MibEntries.LastName',
            'MibEntries.MaidenName',
            'MibEntries.MiddleName',
            'MibEntries.Nationality',
            'MibEntries.Suffix',
            'MibEntries.DownloadStatus',
            'MibEntries.IsShared',
            'MibEntries.IsDeactivated',
            'MibEntries.BirthPlace',
            'MibEntries.PolicyNumber',
            'MibEntries.UnderwritingDate',
            'MibEntries.ActionCode',
            'MibEntries.CompanyId',
            'MibEntries.CompanyCode',
            'MibImpairments.ImpairmentDate',
            'MibImpairments.ReportedDate',
            'MibImpairments.EncodeDate',
            'MibImpairments.Remarks',
            'MibImpairments.NewImpairmentCode',
            'MibImpairments.vr',
            'MibImpairments.LetterCode',
            'MibImpairments.ImpairmentCodes',
            'MibImpairments.NumberCode');
        $query->addSelect(DB::raw("CONCAT(MibEntries.LastName, ', ', MibEntries.FirstName, ' ', MibEntries.MiddleName) AS FullName"));

        // Capture the sortBy and sortType frm the request
        if ($request->sortBy)
            $query->orderBy($request->sortBy, $request->sortType ?? 'ASC');

        // Paginate the results
        $data = $query->paginate($request->rowsPerPage ?? 10);

        // Return the paginated data as JSON
        return response()->json($data);
    }

    public function countEntries(Request $request)
    {
        $resetTimestamp = DB::table('system_config')
            ->where('key', 'monitoring_reset_timestamp')
            ->value('value');

        $startDate = $resetTimestamp ?: '1970-01-01';  // Default if no reset timestamp found

        // get total number of inquiries (entry) from MibEntry_Audit
        $inquiries = EntryAudit::select('CompanyId',
            DB::raw('MAX(created_at) as LastRequestDate'),
            DB::raw('COUNT(Id) as Total'))
            ->whereNotNull('created_at')
            ->where('created_at', '>=', $startDate)
            ->groupBy('CompanyId')
            ->get();

        foreach ($inquiries as $inquiry) {
            // get total number of entries
            $totalEntries = Entry::where('CompanyId', $inquiry->CompanyId)
                ->whereNotNull('created_at')
                ->where('created_at', '>=', $startDate)
                ->count();

            if ($totalEntries > 0 || $inquiry->Total > 0) {
                $result[$inquiry->CompanyId] = [
                    'CompanyName' => $inquiry->CompanyName,
                    'TotalEntries' => $totalEntries,
                    'TotalInquiries' => $inquiry->Total,
                    'LastRequestDate' => $inquiry->LastRequestDate
                ];
            }
        }

        $collection = collect($result ?? []);

        // Capture the sortBy and sortType from the request
        $data = $request->sortType == 'desc'
                ? $collection->sortByDesc($request->sortBy ?? 'CompanyName')
                : $collection->sortBy($request->sortBy ?? 'CompanyName');

        // Paginate the results
        $data = PaginationHelper::paginate($data ?? [], $request->rowsPerPage ?? 10);

        return response()->json($data);
    }
}
