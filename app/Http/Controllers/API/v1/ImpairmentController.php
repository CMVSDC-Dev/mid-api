<?php

namespace App\Http\Controllers\API\v1;


use Carbon\Carbon;
use App\Models\Entry;
use App\Models\Letter;
use App\Models\Number;
use App\Models\ActionCode;
use App\Models\Impairment;
use Illuminate\Http\Request;
use App\Models\ImpairmentCode;
use App\Helpers\GeneralHelpers;
use App\Models\ImpairmentAudit;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImpairmentResource;
use App\Http\Resources\ImpairmentCollection;

class ImpairmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/impairment",
     *     tags={"Impairment"},
     *     summary="Get Impairment",
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="500", description="Login Required"),
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(Request $request)
    {
        // Build a query
        $query = Impairment::query();

        // Capture the searchField and searchValue from the request
        if ($request->searchField && $request->searchValue)
            // Apply the search filter
            $query->where($request->searchField, 'like', '%' . $request->searchValue . '%');


        // Capture the sortBy and sortType from the request
        if ($request->sortBy)
            $query->orderBy($request->sortBy, $request->sortType ?? 'ASC');

        // Paginate the results
        $data = $query->paginate($request->rowsPerPage ?? 10);

        // Return the paginated data as JSON
        return response()->json($data);
        // return new ImpairmentCollection(Impairment::limit(5)->get());
    }

    /**
     * @OA\Get(
     *     path="/api/impairment/{id}",
     *     tags={"Impairment"},
     *     summary="Get a single impairment by ID",
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
    public function show(Request $request, Impairment $impairment)
    {
        if ($impairment)
            $this->createAuditTrail($request, $impairment, 'View Details'); // mib impairment entry audit trail

        return new ImpairmentResource($impairment);
    }

    /**
     * @OA\Post(
     *     path="/api/impairment",
     *     tags={"Impairment"},
     *     summary="Store Impairment",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="MemberControlNumber",
     *         in="query",
     *         description="Member Control Number",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="NewImpairmentCode",
     *         in="query",
     *         description="New Impairment Code",
     *         required=true,
     *         @OA\Schema(type="string", enum={"A1","A10","A100","A11","A12","A13","A13A","A13B","A13C","A15","A15A","A15B","A16","A17","A17A","A17B","A17C","A17D","A17E","A18","A19","A2","A20","A21","A22","A23","A24","A25","A26","A27","A27A","A3","A4","A4A","A4B","A4C","A4D","A5","A5A","A5C","A5D","A5E","A6","A7","A7A","A7B","A8","A9","B1","B10","B100","B11","B12","B13","B14","B15","B16","B17","B18","B19","B1A","B1B","B2","B20","B22","B23","B24","B25","B26","B27","B2A","B2B","B3","B4","B5","B6","B7","B8","B9","C 31","C 31a","C01","C02","C03","C04","C05","C06","C07","C08","C09","C10","C100","C11","C12","C13","C14","C15","C16","C17","C18","C19","C20","C21","C22","C23","C24","C25","C26","C27","C28","C29","C30","C32","C33","C34","C35","C36","C37","C38","C39","C39a","C40","C41","C42","C43","C44","C45","C46","C47","C48","C49","C50","C51","C52","C53","C54","C55","CH1","CH100","CH2","CH2A","CH2B","CH2C","CH2D","CH2E","CH2F","CH2G","CH3","CH4","CH4A","CH4B","CH4C","CH4D","CH4E","CH4F","CH4G","CH5","Cpn","Cpn100","Cpn56","Cpn57","Cpn58","Cpn59","Cpn60","Cpn61","Cpn62","Cpn63","Cpn64","Cpn65","Cpn66","Cpn67","D","D1","D10","D100","D10A","D10B","D11","D11A","D11B","D11C","D11D","D11E","D11F","D12","D13","D14","D14A","D14B","D14C","D15","D16","D16A","D16B","D16C","D17","D17A","D17B","D17C","D17D","D17E","D17F","D18","D18A","D18B","D19","D1A","D1B","D1C","D1D","D1E","D1F","D1G","D2","D20","D21","D22","D22B","D23","D24","D24A","D24B","D24C","D24D","D25","D25A","D25B","D25C","D26","D26A","D26B","D27","D28","D28A","D28B","D28C","D28D","D29","D2A","D2B","D3","D30","D30A","D30B","D31","D32","D32A","D32B","D32C","D32D","D32E","D33D","D33D1","D33E","D33F","D33G","D33G2","D33H","D33H1","D33H2","D33H3","D33H4","D33I","D33J","D33K","D33K1","D33L1","D33L2","D33L3","D33L4","D33L5","D33M","D33M1","D33N","D33N1","D33O","D33O1","D33O2","D33O3","D33P","D33P1","D33P2","D33P3","D33P4","D33Q","D33R","D33R1","D33R2","D33R3","D33S","D33S1","D33S2","D33S3","D33S4","D33S5","D33T","D33T1","D33T2","D33T3","D33T4","D33T5","D34","D34A","D34B","D34C","D34D","D35","D35A","D35B","D35C","D36","D36A","D36B","D36C","D36D","D36E","D37","D37A","D37B","D37C","D37D","D38","D3A","D4","D4A","D4B","D4C","D5","D5A","D5B","D6","D6A","D6B","D7","D7A","D7B","D8","D8A","D9","D9A","D9B","D9C","D9D","E","E01","E01A","E02","E02A","E02B","E02C","E02D","E02E","E02X","E03","E04","E05","E06","E07","E08","E09","E10","E100","E11","E12","E13","E14","E15","E16","E16A","E16B","E16C","E16D","E17","E18","E18SX","E19","E20","E21","E22","E23","E24","E25","E26","E27","E28","E29","E30","E31","E32","E33","E34","E35","E38","E39","E40","E41","E42","E43","E44","E45","E46","F","F01","F02","F03","F04","F05","F05A","F06","F07","F08","F09","F10","F100","F11","F12","F13","F14","F15","F16","F17","F18","F19","F20","F21","F22","F23","F24","F25","F26","F27","F28","F29","F30","F31","FH1","FH10","FH100","FH11","FH12","FH13","FH14","FH15","FH16","FH17","FH18","FH19","FH1A","FH1B","FH1C","FH1D","FH1E","FH1F","FH1G","FH1H","FH1I","FH2","FH20","FH21","FH22","FH23","FH25","FH3","FH4","FH5","FH6","FH7","FH8","FH9","H01","H01A","H02","H03","H04","H05","H06","H06A","H07","H08","H08A","H09","H10","H100","H11","H12","H13","H14","H15","H16","H17","H18","H19","H19A","H19B","H19C","H19D","H19E","H19F","H19G","H19H","H20","H20A","H20B","H20C","H20D","H20E","H21","H21A","H22","H22A","H22B","H23","H23A","H23B","H23C","H23D","H23F","H24","H24A","H24B","H25","H25A","H25B","J01","J02","J03","J04","J05","J05A","J06","J07","J08","J09","J10","J100","J11","J12","J13","J13A","J14","J15","J16","J17","J18","J19","J20","J21","J22","J23","J24","J25","J26","J27","J28","J29","J30","J31","J32","J33","J34","J35","J36","J37","J38","J39","J40","J41","J42","J43","J44","J45","J46","J47","J48","J48a","J49","Jpn100","Jpn50","Jpn51","Jpn53","Jpn53a","Jpn53b","Jpn54","Jpn55","K01","K02","K03","K04","K05","K06","K07","K08","K09","K10","K100","K11","K12","K13","K14","K15","K16","K17","K18","K19","K20","K21","K22","K23","K24","K25","K26","K27","K28","K29","K30","K31","K32","K33","K34","K35","K38","K39","K40","K41","K42","K43","K44","K45","K46","K47","K48","L01","L02","L03","L04","L05","L06","L07","L08","L09","L10","L100","L11","L12","L13","L14","L14A","L15","L16","L16a","L17","L18","L19","L20","L21","L22","L23","L24","L25","L26","L27","L28","L29","L30","L31","L32","L34","L35","L36","L37","L38","L39","L40","L41","L42","L43","L44","L45","L46","L47","L48","L49","L50","L51","L52","L53","L54","L55","L56","L57","L58","L59","L60","L61","L62","L63","L64","L65","L65A","L66","L67","L68","L69","L70","L71","L71X","L72","L73","L74","L75","L76","L77","L78","L79","M","M01","M02","M03","M04","M05","M06","M07","M08","M09","M10","M100","M12","M13","M14","M15","M16","M17","M18","M19","M20","M21","M22","M22A","M23","M24","M25","M25A","M25B","M25C","M25E","M26","M27","M28","MX","MX1","MX100","MX2","MX2A","MX3","MX4","MX4A","MX4B","MX4C","MX4D","MX5","MX6","MX7","MX8","N1","N10","N100","N11","N12","N13","N13A","N13B","N13C","N13D","N13E","N14","N15","N16","N17","N18","N19","N2","N20","N21","N21A","N23","N25","N26","N27","N28","N29","N2A","N3","N30","N31","N32","N33","N34","N35","N36","N37","N38","N39","N3A","N3B","N3C","N3D","N3E","N4","N40","N41","N42","N43","N48","N5","N6","N7","N7a","N7b","N7c","N7d","N8","N8a","N9","NA-","NN","NN01","NN02","NN03","NN04","NN05","NN06","NN07","NN08","NN09","NN10","NN100","NN11","NN11a","NN11b","NN12","NN13","NN14","NN15","NN16","NN17","P01","P02","P03","P04","P05","P06","P06A","P08","P09","P10","P100","P11","P12","P13","P14","P15","P15A","P17","P18","P20","P21","P22","P23","P24","P25","P26","P27","P28","P29","P30","P30A","P31","P32","P33","P34","P35","P36","P37","P38","PMH1","PMH10","PMH100","PMH11","PMH12","PMH13","PMH14","PMH15","PMH16","PMH17","PMH18","PMH19","PMH1A","PMH2","PMH20","PMH21","PMH21A","PMH21B","PMH22","PMH22A","PMH23","PMH24","PMH25","PMH26","PMH27","PMH28","PMH28A","PMH28B","PMH28C","PMH28D","PMH28E","PMH28F","PMH29","PMH3","PMH30","PMH31","PMH32","PMH33","PMH34","PMH34A","PMH34B","PMH34C","PMH34D","PMH35","PMH36","PMH36A","PMH37","PMH38","PMH39","PMH4","PMH40","PMH41","PMH42","PMH43","PMH44","PMH45","PMH46","PMH47","PMH48","PMH49","PMH5","PMH50","PMH51","PMH52","PMH53","PMH6","PMH7","PMH8","PMH9","R01","R02","R02A","R03","R05","R06","R07","R07A","R07B","R07C","R07D","R08","R09","R09A","R09B","R09-BI","R09-BI1","R09-BI2","R09-BI3","R09C","R09D","R100","R12","R13","R14","R15","R16","R17"})
     *     ),
     *     @OA\Parameter(
     *         name="NumberCode",
     *         in="query",
     *         description="Number Code",
     *         required=true,
     *         @OA\Schema(type="string", enum={"0","1","2","3","4","5","6","7"})
     *     ),
     *     @OA\Parameter(
     *         name="Letter Code",
     *         in="query",
     *         description="Letter Code",
     *         required=true,
     *         @OA\Schema(type="string", enum={"M","W","X","Y","Z","NA"})
     *     ),
     *     @OA\Parameter(
     *         name="ImpairmentDate",
     *         in="query",
     *         description="Impairment Date",
     *         required=false,
     *         @OA\Schema(type="date", format="date", example="YYYY-MM-DD")
     *     ),
     *     @OA\Parameter(
     *         name="ReportedDate",
     *         in="query",
     *         description="Reported Date",
     *         required=true,
     *         @OA\Schema(type="date", format="date", example="YYYY-MM-DD")
     *     ),
     *     @OA\Parameter(
     *         name="EncodeDate",
     *         in="query",
     *         description="Encode Date",
     *         required=false,
     *         @OA\Schema(type="date", format="date", example="YYYY-MM-DD")
     *     ),
     *
     *     @OA\Parameter(
     *         name="IsShared",
     *         in="query",
     *         description="Is Shared",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Parameter(
     *         name="Remarks",
     *         in="query",
     *         description="Remarks",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="OldImpairmentCode",
     *         in="query",
     *         description="Old Impairment Code",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="IsDeactivated",
     *         in="query",
     *         description="Deactivated",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="ActionCode",
     *         in="query",
     *         description="Action Code",
     *         required=true,
     *         @OA\Schema(type="string", enum={"A","B","C","D","DR","I","P","R","RB","Rec","RR","S","NA"})
     *     ),
     *     @OA\Parameter(
     *         name="UnderwritingDate",
     *         in="query",
     *         description="Underwriting Date",
     *         required=true,
     *         @OA\Schema(type="date", format="date", example="YYYY-MM-DD")
     *     ),
     *     @OA\Parameter(
     *         name="vr",
     *         in="query",
     *         description="VR",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Login successful"),
     *     @OA\Response(response="401", description="Invalid credentials")
     * )
     */
    public function store(Request $request, Impairment $impairment)
    {
        $validated = $request->validate([
            'MemberControlNumber'=> 'required|max:255',
            'NewImpairmentCode' => 'max:50',
            'NumberCode'        => 'required|max:50',
            'LetterCode'        => 'required|max:50',
            'ImpairmentDate'    => 'date',
            'ReportedDate'      => 'required|date',
            'EncodeDate'        => 'date',
            'IsShared'          => 'boolean',
            'Remarks'           => 'max:255',
            'OldImpairmentCode' => 'max:255',
            'IsDeactivated'     => 'boolean',
            'ActionCode'        => 'required|max:50',
            'UnderwritingDate'  => 'required|date',
            'vr'                => 'string|max:25',
            'ImpairmentCodes'   => 'max:255',
        ]);

        // Get the Id of the Member using the MemberControlNumber
        $Entry = Entry::select('Id')->where('MemberControlNumber', '=', $validated['MemberControlNumber'])->first();
        $validated['MibEntryId'] = $Entry->Id;

        if (!empty($validated['NewImpairmentCode']) || !empty($validated['ImpairmentCodes'])) {
            // Get the Impairment Code Id of the ImpairmentCode
            $ImpairmentCode = ImpairmentCode::select('Id')
                ->where('Code', '=', $validated['NewImpairmentCode'])
                ->orWhere('Code', '=', $validated['ImpairmentCodes'])
                ->first();
            $validated['ImpairmentCodeId'] = $ImpairmentCode->Id;

            // If ImpairmentCodes is empty, use value from NewImpairmentCode
            $validated['ImpairmentCodes'] = $validated['ImpairmentCodes'] ?: $validated['NewImpairmentCode'];
        }

        // Get Number Id of the NumberCode
        $Number = Number::select('Id')->where('Code', '=', $validated['NumberCode'])->first();
        $validated['NumberId'] = $Number->Id;

        // Get Letter Id of the LetterCode
        $Letter = Letter::select('Id')->where('Code', '=', $validated['LetterCode'])->first();
        $validated['LetterId'] = $Letter->Id;

        // Get Action Code Id of the ActionCode
        $ActionCode = ActionCode::select('Id')->where('Code', '=', $validated['ActionCode'])->first();
        $validated['ActionCodeId'] = $ActionCode->Id;

        // make default values using API requests
        $validated['IsShared'] = true;
        $validated['IsDeactivated'] = false;

        // Store in Database
        $impairment = Impairment::create($validated);

        if ($impairment)
            $this->createAuditTrail($request, $impairment, 'Insert Entry'); // mib impairment entry audit trail

        return new ImpairmentResource($impairment);
    }

    /**
     * Create Impairment Inquiry Audit Trail
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Impairment $impaiment
     * @param string $action (eg: Insert Entry|View Details|Update Details|Share|Share Request|Change Request)
     * @return void
     */
    public function createAuditTrail(Request $request, Impairment $impairment, string $action)
    {
        $helper = new GeneralHelpers();
        // $newImpairmentCode = ImpairmentCode::where('Id', $impairment->ImpairmentCodeId)->first()->Code;
        ImpairmentAudit::create([
            'EntryID'           => $impairment->Id ?? $impairment->id,
            'MibEntryId'        => $impairment->MibEntryId,
            'ImpairmentCodeId'  => $impairment->ImpairmentCodeId,
            'NewImpairmentCode' => $impairment->NewImpairmentCode,
            'OldImpairmentCode' => $impairment->OldImpairmentCode,
            'NumberId'          => $impairment->NumberId,
            'LetterId'          => $impairment->LetterId,
            'ImpairmentDate'    => $impairment->ImpairmentDate,
            'ReportedDate'      => $impairment->ReportedDate,
            'EncodeDate'        => $impairment->EncodeDate,
            'IsShared'          => $impairment->IsShared,
            'IsDeactivated'     => $impairment->IsDeactivated,
            'Remarks'           => $impairment->Remarks,
            'ActionCodeId'      => $impairment->ActionCodeId,
            'UnderwritingDate'  => $impairment->UnderwritingDate,
            'vr'                => $impairment->vr,
            'DateOfAction'      => Carbon::now(),
            'PersonInvolved'    => $helper->getAuthUser(),
            'ActionDone'        => $action,
        ]);
    }
}
