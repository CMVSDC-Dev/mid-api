<?php

namespace App\Http\Controllers\API;

use App\Models\Entry;
use App\Models\Member;
use App\Models\Upload;
use App\Models\Company;
use App\Models\Download;
use App\Models\EntryAudit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    public function getCount()
    {
        $from = request('start') ?: date('Y-m-d');
        $to = request('end') ?: date('Y-m-d');

        $query = Entry::query();

        $data = $query->first();

        $data->total = [
            'companies' => Company::count(),
            'members' => Member::count(),
            'uploads' => Upload::count(),
            'downloads' => Download::count(),
            'entries' => Entry::count(),
            'inquiries' => EntryAudit::count(),
        ];

        $data->date = $from == date('Y-m-d') && $to == date('Y-m-d') ? '(Today)' : '';
        return response()->json($data);
    }

    public function getYears()
    {
        $years = Entry::select(\DB::raw('YEAR(date) as year'))->distinct()->get();
        return response()->json($years);
    }

    public function geYearlyData()
    {
        $year = request('year') ?: date('Y');
        $result = \DB::select("SELECT `month`, `total`
        FROM (SELECT
                DATE_FORMAT(`date`, '%m') AS `m`,
                DATE_FORMAT(`date`, '%b') AS `month`,
                COUNT(Id) AS `total`
            FROM `MibEntries`
            WHERE YEAR(`date`) = $year AND deleted_at IS NULL
            GROUP BY `m`, `month`) t
        ORDER BY `m` ASC");

        $collection = collect($result);
        $data['status'] = [
            [
                'name' => 'total',
                'data' => $collection->pluck('total')
            ],
        ];
        $data['months'] = $collection->pluck('month');

        return response()->json($data);
    }

    public function getMonths()
    {
        $year = request('year') ?: date('Y');
        $months = Entry::select(\DB::raw("DATE_FORMAT(date, '%m') AS m, DATE_FORMAT(date, '%M') AS month"))
            ->whereYear('date', $year)
            ->distinct()
            ->orderBy('m', 'ASC')
            ->get();
        return response()->json($months);
    }

    public function getMonthlyData()
    {
        $year = request('year') ?: date('Y');
        $month = request('month') ?: date('M');
        $query = Entry::whereYear('date', $year)
            ->whereMonth('date', $month);

        $result = $query->first();
        $total = $result->total;
        foreach($result as $key => $value) {
            if($key !== 'total') {
                $data['labels'][] = round((($value / $total) * 100), 1) . '% ' . $key;
                $data['series'][] = $value;
            }
        }

        return response()->json($data);
    }
}
