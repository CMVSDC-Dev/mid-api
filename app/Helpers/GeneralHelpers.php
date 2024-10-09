<?php

namespace App\Helpers;

class GeneralHelpers
{
    public function convert_options_to_array($options)
    {
        $converted = [];
        foreach ($options as $option) {
            $converted[$option['val']] = $option['name'];
        }
        return $converted;
    }

    /**
     * Get Latest Version of Release Notes
     */
    public function getLatestVersion()
    {
        foreach (config('release') as $release) {
            return 'v' . array_key_first($release);
        }
    }

    /**
     * Get the Authenticated User
     */
    public function getAuthUser()
    {
        if(request('token')){
            $user = \App\Models\User::where(['jwt_secret' => request('token'), 'is_active' => true])->first();
        } else {
            $user = auth()->user();
        }
        return $user->member_id ?? null;
    }

    /**
     * Format a date range
     * ** Distinct cases:
     * 1. Start and end dates are the same day
     * 2. Start and end dates fall in the same calendar month
     * 3. Start and end dates span calendar month
     */
    public function formatDateRange($start, $end)
    {
        $d1 = new \Datetime($start);
        $d2 = new \Datetime($end);
        if ($d1->format('Y-m-d') === $d2->format('Y-m-d')) {
            # Same day
            return $d1->format('F d, Y');
        } elseif ($d1->format('Y-m') === $d2->format('Y-m')) {
            # Same calendar month
            return $d1->format('F d-') . $d2->format('d, Y');
        } elseif ($d1->format('Y') === $d2->format('Y')) {
            # Same calendar year
            return $d1->format('M d - ') . $d2->format('M d, Y');
        } else {
            # General case (spans calendar years)
            return $d1->format('M d, Y - ') . $d2->format('M d, Y');
        }
    }

}
