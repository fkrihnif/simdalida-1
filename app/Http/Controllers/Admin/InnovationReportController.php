<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InnovationReport;
use App\Models\InnovationProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InnovationReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->roles == 'SUPERADMIN') {
            $report = InnovationReport::orderBy('id', 'DESC')->get();
        } else if (Auth::user()->roles == 'ADMIN') {
            $report = InnovationReport::where('users_id', Auth::user()->id)
                ->orderBy('innovation_profiles_id', 'DESC')
                ->orderBy('quartal', 'DESC')
                ->get();
        }

        return view('pages.admin.innovation-report.index', ['report' => $report]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $innovation = InnovationProfile::where('users_id', Auth::user()->id)->get();
        return view('pages.admin.innovation-report.create', [
            'innovation' => $innovation
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
        $validation = \Validator::make($request->all(), [
            "users_id" => "required",
            "innovation_step" => "required",
            "innovation_initiator" => "required",
            "innovation_type" => "required",
            "innovation_formats" => "required",
            "time_innovation_implement" => "required",
            "problem" => "required",
            "solution" => "required",
            "improvement" => "required",
            "complain_innovation_total" => "required",
            "complain_improvement_total" => "required",
            "achievement_goal_level" => "required",
            "achievement_goal_problem" => "required",
            "benefit_level" => "required",
            "achievement_result_level" => "required",
            "achievement_result_problem" => "required",
            "innovation_strategy" => "required",
            "video_innovation" => "required",
            'quartal' => [
                'required',
                Rule::unique('innovation_reports')->where(function ($query) use ($request) {
                    return $query->where('report_year', $request->get('report_year'))
                        ->where('innovation_profiles_id', $request->get('innovation_profiles_id'))
                        ->where('quartal', $request->get('quartal'));
                }),
            ],
            [
                "quartal.unique" => "Laporan inovasi pada triwulan ini telah ada, silahkan edit pada menu Laporan Inovasi Untuk merubahnya"
            ]
        ])->validate();

        $report = new InnovationReport();
        $ambil = InnovationProfile::where('id', $request->get('innovation_profiles_id'))->first()->name;
        $report->users_id = $request->get('users_id');
        $report->name = $ambil;
        $report->innovation_profiles_id = $request->get('innovation_profiles_id');
        $report->innovation_step = json_encode($request->innovation_step);
        $report->innovation_initiator = json_encode($request->innovation_initiator);
        $report->innovation_type = $request->get('innovation_type');
        $report->innovation_formats = $request->get('innovation_formats');
        $report->time_innovation_implement = $request->get('time_innovation_implement');
        $report->problem = $request->get('problem');
        $report->solution = $request->get('solution');
        $report->improvement = $request->get('improvement');
        $report->complain_innovation_total = $request->get('complain_innovation_total');
        $report->complain_improvement_total = $request->get('complain_improvement_total');
        $report->achievement_goal_level = $request->get('achievement_goal_level');
        $report->achievement_goal_problem = $request->get('achievement_goal_problem');
        $report->benefit_level = $request->get('benefit_level');
        $report->achievement_result_level = $request->get('achievement_result_level');
        $report->achievement_result_problem = $request->get('achievement_result_problem');
        $report->innovation_strategy = $request->get('innovation_strategy');
        $report->video_innovation = $request->get('video_innovation');

        if ($request->file('innovation_sk_file')) {
            $sk = $request->file('innovation_sk_file')->store('laporan/SKinovasi', 'public');
            $report->innovation_sk_file = $sk;
        }

        if ($request->file('complain_innovation_file')) {
            $complain = $request->file('complain_innovation_file')->store('laporan/pengaduanInovasi', 'public');
            $report->complain_innovation_file = $complain;
        }

        if ($request->file('complain_improvement_file')) {
            $improvement = $request->file('complain_improvement_file')->store('laporan/pengaduanTindaklanjuti', 'public');
            $report->complain_improvement_file = $improvement;
        }

        if ($request->file('achievement_goal_level_file')) {
            $achievement = $request->file('achievement_goal_level_file')->store('laporan/capaianTujuanInovasi', 'public');
            $report->achievement_goal_level_file = $achievement;
        }

        if ($request->file('benefit_level_file')) {
            $benefit = $request->file('benefit_level_file')->store('laporan/kemanfaatanInovasi', 'public');
            $report->benefit_level_file = $benefit;
        }

        if ($request->file('achievement_result_level_file')) {
            $arlf = $request->file('achievement_result_level_file')->store('laporan/capaianHasilInovasi', 'public');
            $report->achievement_result_level_file = $arlf;
        }

        $report->report_year = $request->get('report_year');
        $report->quartal = $request->get('quartal');

        $report->save();

        return redirect()->route('innovation-report.index')->with('status', 'Created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = InnovationReport::find($id);
        return view('pages.admin.innovation-report.show', ['item' => $item]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $item = InnovationReport::findOrFail($id);

        return view('pages.admin.innovation-report.edit', [
            'item' => $item
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
        $report = InnovationReport::findOrFail($id);

        \Validator::make($request->all(), [
            "users_id" => "required",
            "name" => "required",
            "innovation_step" => "required",
            "innovation_initiator" => "required",
            "innovation_type" => "required",
            "innovation_formats" => "required",
            "time_innovation_implement" => "required",
            "problem" => "required",
            "solution" => "required",
            "improvement" => "required",
            "complain_innovation_total" => "required",
            "complain_improvement_total" => "required",
            "achievement_goal_level" => "required",
            "achievement_goal_problem" => "required",
            "benefit_level" => "required",
            "achievement_result_level" => "required",
            "achievement_result_problem" => "required",
            "innovation_strategy" => "required",
            "video_innovation" => "required",
            "quartal" => "required",
        ])->validate();

        $report->users_id = $request->get('users_id');
        $report->name = $request->get('name');
        $report->innovation_step = json_encode($request->innovation_step);
        $report->innovation_initiator = json_encode($request->innovation_initiator);
        $report->innovation_type = $request->get('innovation_type');
        $report->innovation_formats = $request->get('innovation_formats');
        $report->time_innovation_implement = $request->get('time_innovation_implement');
        $report->problem = $request->get('problem');
        $report->solution = $request->get('solution');
        $report->improvement = $request->get('improvement');
        $report->complain_innovation_total = $request->get('complain_innovation_total');
        $report->complain_improvement_total = $request->get('complain_improvement_total');
        $report->achievement_goal_level = $request->get('achievement_goal_level');
        $report->achievement_goal_problem = $request->get('achievement_goal_problem');
        $report->benefit_level = $request->get('benefit_level');
        $report->achievement_result_level = $request->get('achievement_result_level');
        $report->achievement_result_problem = $request->get('achievement_result_problem');
        $report->innovation_strategy = $request->get('innovation_strategy');
        $report->video_innovation = $request->get('video_innovation');

        if ($request->file('innovation_sk_file')) {
            if ($report->innovation_sk_file && file_exists(storage_path('app/public/' . $report->innovation_sk_file))) {
                \Storage::delete('public/' . $report->innovation_sk_file);
            }
            $sk = $request->file('innovation_sk_file')->store('laporan/SKinovasi', 'public');
            $report->innovation_sk_file = $sk;
        }

        if ($request->file('complain_innovation_file')) {
            if ($report->complain_innovation_file && file_exists(storage_path('app/public/' . $report->complain_innovation_file))) {
                \Storage::delete('public/' . $report->complain_innovation_file);
            }
            $complain = $request->file('complain_innovation_file')->store('laporan/pengaduanInovasi', 'public');
            $report->complain_innovation_file = $complain;
        }

        if ($request->file('complain_improvement_file')) {
            if ($report->complain_improvement_file && file_exists(storage_path('app/public/' . $report->complain_improvement_file))) {
                \Storage::delete('public/' . $report->complain_improvement_file);
            }
            $improvement = $request->file('complain_improvement_file')->store('laporan/pengaduanTindaklanjuti', 'public');
            $report->complain_improvement_file = $improvement;
        }

        if ($request->file('achievement_goal_level_file')) {
            if ($report->achievement_goal_level_file && file_exists(storage_path('app/public/' . $report->achievement_goal_level_file))) {
                \Storage::delete('public/' . $report->achievement_goal_level_file);
            }
            $ach = $request->file('achievement_goal_level_file')->store('laporan/capaianTujuanInovasi', 'public');
            $report->achievement_goal_level_file = $ach;
        }

        if ($request->file('benefit_level_file')) {
            if ($report->benefit_level_file && file_exists(storage_path('app/public/' . $report->benefit_level_file))) {
                \Storage::delete('public/' . $report->benefit_level_file);
            }
            $benefit = $request->file('benefit_level_file')->store('laporan/kemanfaatanInovasi', 'public');
            $report->benefit_level_file = $benefit;
        }

        if ($request->file('achievement_result_level_file')) {
            if ($report->achievement_result_level_file && file_exists(storage_path('app/public/' . $report->achievement_result_level_file))) {
                \Storage::delete('public/' . $report->achievement_result_level_file);
            }
            $arlf = $request->file('achievement_result_level_file')->store('laporan/capaianHasilInovasi', 'public');
            $report->achievement_result_level_file = $arlf;
        }

        $report->report_year = $request->get('report_year');
        $report->quartal = $request->get('quartal');
        $report->save();

        return redirect()->route('innovation-report.index')->with('status', 'Data successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = InnovationReport::findOrFail($id);
        \Storage::delete('public/' . $item->innovation_sk_file); //utk hapus file di storage agar tidk penuh
        \Storage::delete('public/' . $item->complain_innovation_file);
        \Storage::delete('public/' . $item->complain_improvement_file);
        \Storage::delete('public/' . $item->achievement_goal_level_file);
        \Storage::delete('public/' . $item->benefit_level_file);
        \Storage::delete('public/' . $item->achievement_result_level_file);
        $item->delete();

        return redirect()->route('innovation-report.index')->with('status', 'Data successfully deleted');
    }
}
