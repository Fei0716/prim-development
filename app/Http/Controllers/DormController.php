<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Exports\OutingExport;
use App\Exports\ResidentExport;
use App\Exports\DormExport;
use App\Imports\DormImport;
use App\Imports\ResidentImport;
use App\Exports\AllStudentlistExport;
use App\Exports\DormStudentlistExport;
use Illuminate\Http\Request;
use App\Models\Dorm;
use App\Models\Outing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherExport;
use App\Imports\TeacherImport;
use App\Models\Organization;
use App\Models\OrganizationRole;
use App\User;
use Illuminate\Validation\Rule;
use App\Models\TypeOrganization;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isNull;

class DormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    //
    //
    //index functions
    public function index()
    {
        //
        // 有error display 不到
        $organization = $this->getOrganizationByUserId();

        $roles = DB::table('organization_roles')
            ->join('organization_user as ou', 'ou.role_id', '=', 'organization_roles.id')
            ->where([
                ['ou.user_id', Auth::id()],
                ['ou.organization_id', $organization[0]->id],
            ])
            ->value('organization_roles.nama');

        // dd($roles);
        return view('dorm.index', compact('roles', 'organization'));
    }

    public function indexOuting()
    {
        // 
        $organization = $this->getOrganizationByUserId();

        return view('dorm.outing.index', compact('organization'));
    }

    public function indexResident($id)
    {
        // 
        $userId = Auth::id();
        $organization = $this->getOrganizationByUserId();
        // dd($organization[0]->id);
        if (
            Auth::user()->hasRole('Superadmin') || Auth::user()->hasRole('Pentadbir') ||
            Auth::user()->hasRole('Guru') || Auth::user()->hasRole('Warden')
        ) {
            $dorm = DB::table('dorms')
                ->select('dorms.organization_id', 'dorms.id as id', 'dorms.name')
                ->where([
                    // ['organizations.id', $organization[0]->id],
                    ['dorms.id', $id],
                ])
                ->orderBy('dorms.name')
                ->get();
        }

        // dd($dorm);
        return view("dorm.resident.index", compact('dorm', 'organization'));
    }

    public function indexDorm()
    {
        // 
        $organization = $this->getOrganizationByUserId();

        $dormlist = DB::table('dorms')
            ->select('id', 'name')
            ->get();


        return view('dorm.management.index', compact('organization', 'dormlist'));
    }

    public function indexStudentlist()
    {
        // 
        $organization = $this->getOrganizationByUserId();

        $studentlist = DB::table('class_student')
            ->whereNotNull('dorm_id')
            ->get();

        $dormlist = DB::table('dorms')
            ->select('id', 'name')
            ->get();

        // return redirect('/dorm/dorm/getAllStudentlistDatatable')->with('success', 'Dorms have been added successfully');

        return view('dorm.studentlist.index', compact('organization', 'studentlist', 'dormlist'));
    }

    //
    //
    //import and export functions
    public function outingexport(Request $request)
    {
        $this->validate($request, [
            'organ'      =>  'required',
        ]);

        $filename = DB::table('organizations')
            ->where('organizations.id', $request->organ)
            ->value('organizations.nama');

        return Excel::download(new OutingExport($request->organ), $filename . ' masa outing.xlsx');
    }

    public function dormexport(Request $request)
    {
        return Excel::download(new DormExport($request->organ), 'dorm.xlsx');
    }

    public function allstudentlistexport(Request $request)
    {
        return Excel::download(new AllStudentlistExport($request->organ), 'studentlist.xlsx');
    }

    public function dormstudentlistexport(Request $request)
    {
        return Excel::download(new DormStudentlistExport($request->organ, $request->dorm), 'studentlist.xlsx');
    }

    public function residentexport(Request $request)
    {
        $this->validate($request, [
            'organExport'      =>  'required',
            'dormExport'      =>  'required',
        ]);

        $filename = DB::table('dorms')
            ->where('dorms.id', $request->dormExport)
            ->value('dorms.name');

        // dd($filename);

        return Excel::download(new ResidentExport($request->organExport, $request->dormExport), $filename . ' pelajar.xlsx');
    }

    public function dormimport(Request $request)
    {
        $file       = $request->file('file');
        $namaFile   = $file->getClientOriginalName();
        $file->move('uploads/excel/', $namaFile);

        $etx = $file->getClientOriginalExtension();
        $formats = ['xls', 'xlsx', 'ods', 'csv'];
        if (!in_array($etx, $formats)) {

            return redirect('/dorm/dorm/indexDorm')->withErrors(['format' => 'Only supports upload .xlsx, .xls files']);
        }

        Excel::import(new DormImport($request->organ), public_path('/uploads/excel/' . $namaFile));

        return redirect('/dorm/dorm/indexDorm')->with('success', 'Dorms have been added successfully');
    }

    public function residentimport(Request $request)
    {
        // dd($request->dorm);
        $file       = $request->file('file1');
        $namaFile   = $file->getClientOriginalName();
        $check = 0;
        if ($check == 0) {
            $file->move('uploads/excel/', $namaFile);
        }

        $etx = $file->getClientOriginalExtension();
        $formats = ['xls', 'xlsx', 'ods', 'csv'];
        if (!in_array($etx, $formats)) {
            return redirect('/dorm/dorm/indexDorm')->withErrors(['format' => 'Only supports upload .xlsx, .xls files']);
        }

        //get the accomodate number for the particular dorm
        $accomodate_number = DB::table('dorms')
            ->where('id', $request->dorm)
            ->value('accommodate_no');

        $student_inside = DB::table('dorms')
            ->where('id', $request->dorm)
            ->value('student_inside_no');

        if ($student_inside <= $accomodate_number) {
            Excel::import(new ResidentImport($request->dorm), public_path('/uploads/excel/' . $namaFile));
            return redirect('/dorm/dorm/indexDorm')->with('success', 'Residents have been added successfully');
        } else
            return redirect('/dorm/dorm/indexDorm')->with('fail', 'Residents have not been added successfully because the dorm is full');
    }

    //report function
    public function reportPerStudent($id)
    {
        $studentName = DB::table('class_student')
            ->join('students', 'students.id', '=', 'class_student.student_id')
            ->where('class_student.id', $id)
            ->select('students.nama as studentName')
            ->first();

        $applicationCat = DB::table('classifications')
            ->get();

        $postId = DB::table('student_outing')
            ->join('organization_user as ou', 'ou.id', '=', 'student_outing.warden_id')
            // ->join('organization_user as ou', 'ou.id', '=', 'student_outing.guard_id')
            ->join('users', 'users.id', '=', 'ou.user_id')
            ->where('student_outing.class_student_id', '=', $id)
            // ->select('student_outing.warden_id as wardenId', 'student_outing.guard_id as guardId')
            ->first();

        dd($studentName, $postId);
        $warden = DB::table('users')
            ->join('organization_user', 'organization_user.user_id', '=', 'users.id')
            ->where('organization_user.id', $postId->wardenId)
            ->select('users.name')
            ->first();



        return view('dorm.report.reportPerStudent', compact('studentName', 'applicationCat'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    //
    //
    //create or add files
    public function create()
    {
        //get user id
        // $userid     = Auth::id();
        $organization = $this->getOrganizationByUserId();

        $category = DB::table('classifications')
            ->where('classifications.organization_id', $organization[0]->id)
            ->get();

        $outingdate = date('Y-m-d', strtotime(DB::table('outings')
            ->where([
                ['outings.organization_id', $organization[0]->id],
                ['outings.end_date_time', '>', now()],
            ])
            ->orderBy("outings.start_date_time")
            ->value("outings.start_date_time as start_date_time")));

        if (Auth::user()->hasRole('Penjaga')) {
            return view('dorm.create', compact('organization', 'category', 'outingdate'));
        }
    }

    public function createOuting()
    {
        //
        $organization = $this->getOrganizationByUserId();
        return view('dorm.outing.add', compact('organization'));
    }

    public function createResident()
    {
        // $userid     = Auth::id();
        $organization = $this->getOrganizationByUserId();

        $dormlist =  $this->getDormByOrganizationId();
        return view('dorm.resident.add', compact('dormlist', 'organization'));
    }

    public function createDorm()
    {
        //
        $organization = $this->getOrganizationByUserId();
        return view('dorm.management.add', compact('organization'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    //
    //
    // store functions
    public function store(Request $request)
    {
        // 
        $this->validate($request, [
            'name'         =>  'required',
            'email'        =>  'required',
            'category'     =>  'required',
            'reason'       =>  'required',
            'start_date'   =>  'required',
            'organization' =>  'required',
        ]);

        $classstudentid = DB::table('students')
            ->join('class_student', 'class_student.id', '=', 'students.id')
            ->join('users', 'users.telno', '=', 'students.parent_tel')
            ->where([
                ['students.nama', $request->get('name')],
                ['students.email', $request->get('email')],
                ['class_student.outing_status', 0],
                ['class_student.blacklist', 0],
                ['users.id', Auth::id()],
            ])
            ->value("class_student.id");

        $outingtype = DB::table('classifications')
            ->where([
                ['classifications.id', $request->get('category')],
            ])
            ->value('classifications.name');

        if (strtoupper($outingtype) == "OUTINGS") {
            $outingid = DB::table('outings')
                ->where('outings.organization_id', $request->get('organization'))
                ->where('outings.start_date_time', '>=', $request->get('start_date'))
                ->where('outings.end_date_time', '>', $request->get('start_date'))
                ->value('outings.id');
        } else {
            $outingid = NULL;
        }

        if(isset($classstudentid)){
            DB::table('student_outing')
            ->insert([
                'reason'            => $request->get('reason'),
                'apply_date_time'   => $request->get('start_date'),
                'status'            => 0,
                'classification_id' => $request->get('category'),
                'class_student_id'  => $classstudentid,
                'outing_id'         => $outingid,
                'created_at'        => now(),
            ]);
    
            return redirect('/dorm')->with('success', 'New application has been added successfully');
        } else {
            return redirect('/dorm')->withErrors('Information not matched');
        }
    }

    public function storeOuting(Request $request)
    {
        // 
        $this->validate($request, [
            'start_date'        =>  'required',
            'end_date'          =>  'required',
            'organization'      =>  'required',
        ]);

        DB::table('outings')->insert([
            'start_date_time' => $request->get('start_date'),
            'end_date_time'   => $request->get('end_date'),
            'organization_id' => $request->get('organization'),
        ]);

        return redirect('/dorm/dorm/indexOuting')->with('success', 'New outing date and time has been added successfully');
    }

    public function storeResident(Request $request)
    {
        // 
        $this->validate($request, [
            'name'              =>  'required',
            'organization'      =>  'required',
            'email'             =>  'required',
            'dorm'              =>  'required'
        ]);

        $organizationid = $request->get('organization');
        $neworganizationid = (int)$organizationid;

        $stdname = $request->get('name');
        $stdemail = $request->get('email');

        $dormid = $request->get('dorm');
        $newdormid = (int)$dormid;

        // find student id
        $student = DB::table('students')
            ->where('students.nama', $stdname)
            ->where('students.email', $stdemail)
            ->select('students.id')
            ->get();

        $dorm = DB::table('dorms')
            ->where('dorms.id', $newdormid)
            ->get();

        if (
            Auth::user()->hasRole('Superadmin') || Auth::user()->hasRole('Pentadbir') ||
            Auth::user()->hasRole('Guru') || Auth::user()->hasRole('Warden')
        ) {
            if (isset($student[0]->id) && ($dorm[0]->student_inside_no < $dorm[0]->accommodate_no)) {
                $updateDetails = [
                    'cs.dorm_id' => $newdormid,
                    'cs.start_date_time' => now()->toDateTimeString(),
                    'cs.end_date_time' => NULL,
                    'cs.outing_status' => 0,
                    'cs.blacklist' => 0,
                ];

                $result = DB::table('class_student as cs')
                    ->join('class_organization as co', 'co.id', '=', 'cs.organclass_id')
                    ->where([
                        ['cs.student_id', $student[0]->id],
                        ['cs.dorm_id', NULL],
                        ['co.organization_id', $neworganizationid],
                        ['cs.status', 1],
                    ])
                    ->update($updateDetails);
            } else {
                $result = 0;
            }

            if ($result > 0) {
                DB::table('dorms')
                    ->where('dorms.id', $newdormid)
                    ->update(['student_inside_no' => $dorm[0]->student_inside_no + 1]);

                return redirect()->to('/dorm/dorm/indexResident/' . $newdormid)->with('success', 'New student has been added successfully');
            }
        }

        return redirect()->to('/dorm/dorm/indexResident/' . $newdormid)->withErrors(['Failed to add student into dorm', 'Possible problem: Dorm is full  |  Student already has accommodation']);
    }


    public function storeDorm(Request $request)
    {
        // 
        $this->validate($request, [
            'name'        =>  'required|unique:dorms',
            'capacity'    =>  'required',
            'organization'      =>  'required',
            //'name', 'accommodate_no', 'student_inside_no'
        ]);
        //echo ({{ $request->get('organization') }});

        DB::table('dorms')->insert([
            'name' => $request->get('name'),
            'accommodate_no'   => $request->get('capacity'),
            'organization_id' => $request->get('organization'),
            'student_inside_no' => 0
        ]);

        return redirect('/dorm/dorm/indexDorm')->with('success', 'New dorm has been added successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    //
    //
    // edit and update functions
    public function edit($id)
    {
        //
        $studentouting = DB::table('student_outing')
        ->join('class_student as cs', 'cs.id', '=', 'student_outing.class_student_id')
        ->join('students', 'students.id', '=', 'cs.student_id')
        ->join('classifications', 'classifications.id', '=', 'student_outing.classification_id')
        ->where([
            ['student_outing.id', $id],
            ['student_outing.status', 0],
        ])
        ->select('student_outing.id', 'student_outing.apply_date_time', 'students.nama', 'students.email', 
        'student_outing.reason', 'classifications.name as categoryname', 'classifications.organization_id as oid')
        ->first();

        if(isset($studentouting))
        {
            $organization = $this->getOrganizationByUserId();

            $category = DB::table('classifications')
                        ->where('classifications.organization_id', $organization[0]->id)
                        ->get();


            return view('dorm.update', compact('studentouting', 'category', 'organization'));
        }
    }

    public function editOuting($id)
    {
        //  

        $outing = DB::table('outings')
            ->where('outings.id', $id)
            ->select('outings.id', 'outings.start_date_time', 'outings.end_date_time', 'outings.organization_id')
            ->first();

        $organization = $this->getOrganizationByUserId();

        return view('dorm.outing.update', compact('outing', 'organization', 'id'));
    }

    public function editResident($id)
    {
        //  
        // dd($id); class_student.id
        $resident = DB::table('dorms')
            ->join('class_student', 'class_student.dorm_id', '=', 'dorms.id')
            ->join('students', 'students.id', '=', 'class_student.student_id')
            ->select('dorms.id as dorm_id', 'dorms.organization_id', 'dorms.name as dormname', 'class_student.student_id as id', 'students.nama as studentname', 'students.email', 'students.parent_tel')
            ->where([
                ['class_student.id', $id],
                ['class_student.status', 1],
            ])
            ->orderBy('dorms.name')
            ->get();

        $dormlist = DB::table('dorms')
            ->join('organizations', 'organizations.id', '=', 'dorms.organization_id')
            ->select('dorms.id as id', 'dorms.name')
            ->where([
                ['dorms.organization_id', $resident[0]->organization_id]

            ])
            ->orderBy('dorms.name')
            ->get();

        $organization = $this->getOrganizationByUserId();
        return view('dorm.resident.update', compact('resident', 'dormlist', 'organization'));
    }

    public function getID($id)
    {
        return response()->json(["string" => $id]);
    }
    public function editDorm($id)
    {
        //  
        $dorm = DB::table('dorms')
            ->where('dorms.id', $id)
            ->select('dorms.id', 'dorms.name', 'dorms.accommodate_no', 'dorms.student_inside_no', 'organization_id')
            ->first();

        //calculate sum of resident inside this dorm
        $dorm_student_inside = DB::table('class_student')
            ->join('dorms', 'dorms.id', '=', 'class_student.dorm_id')
            ->where('class_student.dorm_id', $id)
            ->count();

        $organization = $this->getOrganizationByUserId();

        return view('dorm.management.update', compact('dorm', 'organization', 'dorm_student_inside', 'id'));
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
        //
        $this->validate($request, [
            'name'         =>  'required',
            'email'        =>  'required',
            'category'     =>  'required',
            'reason'       =>  'required',
            'start_date'   =>  'required',
            'organization' =>  'required',
        ]);

        $classstudentid = DB::table('students')
                ->join('class_student', 'class_student.id', '=', 'students.id')
                ->join('users', 'users.telno', '=', 'students.parent_tel')
                ->where([
                    ['students.nama', $request->get('name')],
                    ['students.email', $request->get('email')],
                    ['class_student.outing_status', 0],
                    ['class_student.blacklist', 0],
                    ['users.id', Auth::id()],
                ])
                ->value("class_student.id");
        
        $outingtype = DB::table('classifications')
        ->where([
            ['classifications.id', $request->get('category')],
        ])
        ->value('classifications.name');

        if(strtoupper($outingtype) == "OUTINGS")
        {
            $outingid = DB::table('outings')
            ->where('outings.organization_id', $request->get('organization'))
            ->where('outings.start_date_time', '>=', $request->get('start_date'))
            ->where('outings.end_date_time', '>', $request->get('start_date'))
            ->value('outings.id');
        }
        else{
            $outingid = NULL; 
        }

        if(isset($classstudentid)){
            DB::table('student_outing')
            ->where('id', $id)
            ->update([
                'reason'            => $request->get('reason'),
                'apply_date_time'   => $request->get('start_date'),
                'status'            => 0,
                'classification_id' => $request->get('category'),
                'class_student_id'  => $classstudentid,
                'outing_id'         => $outingid,
                'updated_at'        => now(),
            ]);
    
            return redirect('/dorm')->with('success', 'The application has been updated');
        }
        else{
            return redirect('/dorm')->withErrors('Information not matched');
        }
    }

    public function updateOuting(Request $request, $id)
    {
        //
        // dd($id);
        $this->validate($request, [
            'start_date'        =>  'required',
            'end_date'          =>  'required',
            'organization'      =>  'required',
        ]);

        DB::table('outings')
            ->where('id', $id)
            ->update(
                [
                    'start_date_time' => $request->get('start_date'),
                    'end_date_time'   => $request->get('end_date')
                ]
            );

        // DB::table('class_organization')->where('class_id', $id)
        //     ->update([
        //         'organization_id' => $request->get('organization'),
        //         'organ_user_id'    =>  $request->get('classTeacher')
        //     ]);

        return redirect('/dorm/dorm/indexOuting')->with('success', 'The data has been updated!');
    }

    public function updateResident(Request $request, $id)
    {
        // dd($id);
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'dorm' => 'required',
            'organization' => 'required'
        ]);

        $organizationid = $request->get('organization');
        $neworganizationid = (int)$organizationid;

        $stdname = $request->get('name');
        $stdemail = $request->get('email');

        $dormid = $request->get('dorm');
        $newdormid = (int)$dormid;

        $dorm = DB::table('dorms')
            ->where([
                ['dorms.id', $newdormid],
            ])
            ->select('dorms.id as dormid', 'dorms.accommodate_no', 'dorms.student_inside_no')
            ->get();


        $resident = DB::table('class_student')
            ->join('students', 'students.id', '=', 'class_student.student_id')
            ->where([
                ['class_student.id', $id],
                ['students.nama', $stdname],
                ['students.email', $stdemail]
            ])
            ->select('class_student.id as id', 'class_student.student_id', 'class_student.dorm_id')
            ->get();


        $olddormid = DB::table('dorms')
            ->where([
                ['dorms.id', $resident[0]->dorm_id],
            ])
            ->select('dorms.id as dormid', 'dorms.accommodate_no', 'dorms.student_inside_no')
            ->get();

        if (
            Auth::user()->hasRole('Superadmin') || Auth::user()->hasRole('Pentadbir') ||
            Auth::user()->hasRole('Guru') || Auth::user()->hasRole('Warden')
        ) {
            if (isset($resident[0]->id) && ($dorm[0]->student_inside_no < $dorm[0]->accommodate_no) && ($olddormid[0]->dormid != $newdormid)) {
                $updateDetails = [
                    'cs.dorm_id' => $newdormid,
                    'cs.start_date_time' => now()->toDateTimeString(),
                    'cs.end_date_time' => NULL,
                ];

                $result = DB::table('class_student as cs')
                    ->join('class_organization as co', 'co.id', '=', 'cs.organclass_id')
                    ->where([
                        ['cs.id', $id],
                        ['co.organization_id', $neworganizationid],
                        ['cs.student_id', $resident[0]->student_id],
                        ['cs.status', 1],
                    ])
                    ->update($updateDetails);
            } else {
                $result = 0;
            }

            if ($result > 0) {
                DB::table('dorms')
                    ->where('dorms.id', $newdormid)
                    ->update(['student_inside_no' => $dorm[0]->student_inside_no + 1]);

                DB::table('dorms')
                    ->where('dorms.id', $olddormid[0]->dormid)
                    ->update(['student_inside_no' => $olddormid[0]->student_inside_no - 1]);

                return redirect()->to('/dorm/dorm/indexResident/' . $newdormid)->with('success', 'New student has been added successfully');
            }
        }
        return redirect()->to('/dorm/dorm/indexResident/' . $newdormid)->withErrors(['Failed to add student into dorm', 'Possible problem: Dorm is full  |  Student not found  |  Student is reside in the dorm']);
    }

    public function updateDorm(Request $request, $id)
    {
        //
        // dd($id);
        $this->validate($request, [
            'name'        =>  'required|unique:dorms',
            'capacity'    =>  'required',
            'organization'      =>  'required',
            //'name', 'accommodate_no', 'student_inside_no'

        ]);

        DB::table('dorms')
            ->where('id', $id)
            ->update(
                [
                    'name' => $request->get('name'),
                    'accommodate_no'   => $request->get('capacity'),
                ]
            );

        return redirect('/dorm/dorm/indexDorm')->with('success', 'The data has been updated!');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //
    //
    // destroy functions
    public function destroy($id)
    {
        //
        $result = DB::table('student_outing')->where('student_outing.id', $id);

        if ($result->delete()) {
            Session::flash('success', 'Permintaan Berjaya Dipadam');
            return View::make('layouts/flash-messages');
        } else {
            Session::flash('error', 'Permintaan Gagal Dipadam');
            return View::make('layouts/flash-messages');
        }
        
    }

    public function destroyOuting($id)
    {
        //
        $result = DB::table('outings')->where('outings.id', $id);

        if ($result->delete()) {
            Session::flash('success', 'Outing Berjaya Dipadam');
            return View::make('layouts/flash-messages');
        } else {
            Session::flash('error', 'Outing Gagal Dipadam');
            return View::make('layouts/flash-messages');
        }
    }

    public function destroyDorm($id)
    {
        //
        $result = DB::table('dorms')->where('dorms.id', $id)->delete();
        //return response()->json($result);
        //$strirng = "asd";
        if ($result) {
            Session::flash('success', 'Dorm Berjaya Dipadam');
            return View::make('layouts/flash-messages');
            //return response()->json(['resultdata' => $result, 'string' => $strirng]);
        } else {
            Session::flash('error', 'Dorm Gagal Dipadam');
            return View::make('layouts/flash-messages');
            //return response()->json(['resultdata' => $result, 'string' => $strirng]);
        }
    }

    public function clearDorm($id)
    {
        //
        $result = DB::table('class_student')->where('dorm_id', $id)->update(['dorm_id' => null, 'end_date_time' => now()->toDateTimeString()]);

        if ($result) {
            DB::table('dorms')->where('id', $id)->update(['student_inside_no' => 0]);

            Session::flash('success', 'Dorm Berjaya Dikosongkan');
            return View::make('layouts/flash-messages');
            //return response()->json(['resultdata' => $result, 'string' => $strirng]);
        } else {
            Session::flash('error', 'Dorm Gagal Dikosongkan');
            return View::make('layouts/flash-messages');
            //return response()->json(['resultdata' => $result, 'string' => $strirng]);
        }
    }

    public function blockStudent($id, $blockStatus)
    {
        // dd("123");
        //if the student is blocked
        if ($blockStatus == 0) {
            $result = DB::table('class_student')
                ->where('class_student.id', '=', $id)
                ->update(['class_student.blacklist' => 0]);
        } else if ($blockStatus == 1) {
            $result = DB::table('class_student')
                ->where('class_student.id', '=', $id)
                ->update(['class_student.blacklist' => 1]);
        }
        if ($result && $blockStatus == 0) {
            Session::flash('success', 'Pelajar Berjaya Unblock');
            return View::make('layouts/flash-messages');
            //return response()->json(['resultdata' => $result, 'string' => $strirng]);
        } else if ($result && $blockStatus == 1) {
            Session::flash('success', 'Pelajar Berjaya Block');
            return View::make('layouts/flash-messages');
        } else {
            Session::flash('error', 'Pelajar Gagal Diproseskan');
            return View::make('layouts/flash-messages');
            //return response()->json(['resultdata' => $result, 'string' => $strirng]);
        }
    }

    public function destroyResident($id)
    {

        // 有没有解决方法呢 不需要duplicate query
        $dorm = DB::table('dorms')
            ->join('class_student', 'class_student.dorm_id', '=', 'dorms.id')
            ->where('class_student.id', $id)
            ->get();

        $updateDetails1 = [
            'class_student.end_date_time' => now()->toDateTimeString(),
            'class_student.dorm_id' => NULL,
            'dorms.student_inside_no' => $dorm[0]->student_inside_no - 1,
        ];

        $result = DB::table('dorms')
            ->join('class_student', 'class_student.dorm_id', '=', 'dorms.id')
            ->where('class_student.id', $id)
            ->update($updateDetails1);

        if ($result) {
            Session::flash('success', 'Pelajar Berjaya Dipadam');
            return View::make('layouts/flash-messages');
        } else {
            Session::flash('error', 'Pelajar Gagal Dipadam');
            return View::make('layouts/flash-messages');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getOrganizationByUserId()
    {
        $userId = Auth::id();
        if (Auth::user()->hasRole('Superadmin')) {

            return Organization::all();
        } else {
            // user role pentadbir 
            return Organization::whereHas('user', function ($query) use ($userId) {
                $query->where('user_id', $userId)->Where(function ($query) {
                    $query->where('organization_user.role_id', '=', 4)
                        ->Orwhere('organization_user.role_id', '=', 5)
                        ->Orwhere('organization_user.role_id', '=', 6)
                        ->Orwhere('organization_user.role_id', '=', 8);
                });
            })->get();
        }
    }

    public function getDormByOrganizationId()
    {
        $userId = Auth::id();
        $organization = $this->getOrganizationByUserId();

        if (Auth::user()->hasRole('Superadmin')) {

            return Dorm::all();
        } else {
            // user role pentadbir 

            return DB::table('dorms')
                ->where('dorms.organization_id', $organization[0]->id)
                ->select()
                ->get();
        }
    }

    //
    //
    //get datatable functions
    public function getOutingsDatatable(Request $request)
    {
        // dd($request->oid);
        if (request()->ajax()) {
            $oid = $request->oid;
            $hasOrganizaton = $request->hasOrganization;

            $userId = Auth::id();

            if ($oid != '' && !is_null($hasOrganizaton)) {

                $data = DB::table('outings')
                    ->select('outings.id', 'outings.start_date_time', 'outings.end_date_time')
                    ->where('outings.organization_id', $oid)
                    ->orderBy('outings.start_date_time');
            }

            $table = Datatables::of($data);

            $table->addColumn('action', function ($row) {
                $token = csrf_token();
                $btn = '<div class="d-flex justify-content-center">';
                $btn = $btn . '<a href="' . route('dorm.editOuting', $row->id) . '" class="btn btn-primary m-1">Edit</a>';
                $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger m-1">Buang</button></div>';
                return $btn;
            });

            $table->rawColumns(['action']);
            return $table->make(true);
        }
    }

    public function getDormDatatable(Request $request)
    {
        // dd($request->oid);
        if (request()->ajax()) {
            $oid = $request->oid;
            $hasOrganizaton = $request->hasOrganization;

            $userId = Auth::id();

            if ($oid != '' && !is_null($hasOrganizaton)) {

                $data = DB::table('dorms')
                    ->select('dorms.id', 'dorms.name', 'dorms.accommodate_no', 'dorms.student_inside_no', 'dorms.organization_id')
                    ->where('dorms.organization_id', $oid)
                    ->orderBy('dorms.name');
                //'name', 'accommodate_no', 'student_inside_no'
            }

            $table = Datatables::of($data);

            $table->addColumn('action', function ($row) {
                $token = csrf_token();
                $btn = '<div class="d-flex justify-content-center">';
                // $btn = $btn . '<a href="' . route('importresident', $row->id) . '" class="btn btn-primary m-1">Import</a>';

                //try
                $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" data-toggle="modal" data-target="#modelId3" class="btn btn-primary m-1 importBtn">Import</button>';

                $btn = $btn . '<a href="' . route('dorm.editDorm', $row->id) . '" class="btn btn-primary m-1">Edit</a>';
                $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger m-1 destroyDorm">Buang</button>';
                $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger m-1 clearDorm">Clear</button></div>';
                return $btn;
            });

            $table->rawColumns(['action']);
            return $table->make(true);
        }
    }

    public function getResidentsDatatable(Request $request)
    {
        // dd($request->hasOrganization);
        if (request()->ajax()) {
            // $oid = $request->oid;

            $dormid = $request->dormid;

            $hasOrganizaton = $request->hasOrganization;

            $userId = Auth::id();

            if ($dormid != '' && !is_null($hasOrganizaton)) {

                $data = DB::table('students')
                    ->join('class_student', 'class_student.student_id', '=', 'students.id')
                    ->join('class_organization', 'class_organization.id', '=', 'class_student.organclass_id')
                    ->join('classes', 'classes.id', '=', 'class_organization.class_id')
                    ->select('students.id as id', 'students.nama as studentname', 'classes.nama as classname', 'class_student.start_date_time', 'class_student.end_date_time', 'class_student.outing_status', 'class_student.blacklist')
                    ->where([
                        ['class_student.dorm_id', $dormid],
                        ['class_student.status', 1],
                    ])
                    ->orderBy('students.nama')
                    ->get();

                $table = Datatables::of($data);

                $table->addColumn('outing_status', function ($row) {
                    if ($row->outing_status == '0') {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-success"> Dalam </span></div>';

                        return $btn;
                    } else {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-danger"> Keluar </span></div>';

                        return $btn;
                    }
                });

                $table->addColumn('blacklist', function ($row) {
                    if ($row->blacklist == '1') {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-danger"> Ya </span></div>';

                        return $btn;
                    } else {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-success"> Tidak </span></div>';

                        return $btn;
                    }
                });

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<a href="' . route('dorm.editResident', $row->id) . '" class="btn btn-primary m-1">Edit</a>';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger m-1">Buang</button></div>';
                    return $btn;
                });

                $table->rawColumns(['outing_status', 'blacklist', 'action']);
                return $table->make(true);
            }
        }
    }

    //for all student 
    public function getAllStudentlistDatatable(Request $request)
    {

        // dd("ser");
        if (request()->ajax()) {
            $oid = $request->oid;

            $hasOrganizaton = $request->hasOrganization;

            $userId = Auth::id();

            if ($oid != '' && !is_null($hasOrganizaton)) {

                $data = DB::table('students')
                    ->join('class_student', 'class_student.student_id', '=', 'students.id')
                    ->join('class_organization', 'class_organization.id', '=', 'class_student.organclass_id')
                    ->join('classes', 'classes.id', '=', 'class_organization.class_id')
                    ->join('dorms', 'dorms.id', '=', 'class_student.dorm_id')
                    ->where('dorms.organization_id',  $oid)
                    ->whereNotNull('class_student.dorm_id')
                    ->select('class_student.id', 'students.nama as studentName', 'classes.nama as className', 'dorms.name as dormName', 'class_student.blacklist as status')
                    ->orderBy('students.nama')
                    ->get();

                $table = Datatables::of($data);

                $table->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-danger"> Blocked </span></div>';

                        return $btn;
                    } else {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-success"> Unblock </span></div>';

                        return $btn;
                    }
                });

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger unblockBtn m-1">Unblock</button>';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger blockBtn m-1">Block</button></div>';
                    $btn = $btn . '<a href="' . route('dorm.reportPerStudent', $row->id) . '" class="btn btn-primary m-1">Report</a>';
                    return $btn;
                });

                $table->rawColumns(['status', 'action']);
                return $table->make(true);
            }
        }
    }

    //for selected dorm student
    public function getDormStudentlistDatatable(Request $request)
    {

        // dd("ser");
        if (request()->ajax()) {
            $dormid = $request->dormid;

            $hasOrganizaton = $request->hasOrganization;

            $userId = Auth::id();

            if ($dormid != '' && !is_null($hasOrganizaton)) {

                $data = DB::table('students')
                    ->join('class_student', 'class_student.student_id', '=', 'students.id')
                    ->join('class_organization', 'class_organization.id', '=', 'class_student.organclass_id')
                    ->join('classes', 'classes.id', '=', 'class_organization.class_id')
                    ->join('dorms', 'dorms.id', '=', 'class_student.dorm_id')
                    ->where('class_student.dorm_id',  $dormid)
                    // ->whereNotNull('class_student.dorm_id')
                    ->select('class_student.id', 'students.nama as studentName', 'classes.nama as className', 'dorms.name as dormName', 'class_student.blacklist as status')
                    ->orderBy('students.nama')
                    ->get();

                $table = Datatables::of($data);

                $table->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-danger"> Blocked </span></div>';

                        return $btn;
                    } else {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-success"> Unblock </span></div>';

                        return $btn;
                    }
                });

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger unblockBtn m-1">Unblock</button>';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger blockBtn m-1">Block</button></div>';
                    $btn = $btn . '<a href="' . route('dorm.reportPerStudent', $row->id) . '" class="btn btn-primary m-1">Report</a>';
                    return $btn;
                });

                $table->rawColumns(['status', 'action']);
                return $table->make(true);
            }
        }
    }

    //for selected dorm but blacklist student
    public function getDormBlacklistStudentlistDatatable(Request $request)
    {

        // dd("ser");
        if (request()->ajax()) {
            $dormid = $request->dormid;

            $hasOrganizaton = $request->hasOrganization;

            $userId = Auth::id();

            if ($dormid != '' && !is_null($hasOrganizaton)) {

                $data = DB::table('students')
                    ->join('class_student', 'class_student.student_id', '=', 'students.id')
                    ->join('class_organization', 'class_organization.id', '=', 'class_student.organclass_id')
                    ->join('classes', 'classes.id', '=', 'class_organization.class_id')
                    ->join('dorms', 'dorms.id', '=', 'class_student.dorm_id')
                    ->where('class_student.dorm_id',  $dormid)
                    ->where('class_student.blacklist', '=', 1)
                    ->select('class_student.id', 'students.nama as studentName', 'classes.nama as className', 'dorms.name as dormName', 'class_student.blacklist as status')
                    ->orderBy('students.nama')
                    ->get();

                $table = Datatables::of($data);

                $table->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-danger"> Blocked </span></div>';

                        return $btn;
                    } else {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-success"> Unblock </span></div>';

                        return $btn;
                    }
                });

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger unblockBtn m-1">Unblock</button>';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger blockBtn m-1">Block</button></div>';
                    $btn = $btn . '<a href="' . route('dorm.reportPerStudent', $row->id) . '" class="btn btn-primary m-1">Report</a>';
                    return $btn;
                });

                $table->rawColumns(['status', 'action']);
                return $table->make(true);
            }
        }
    }

    //for all blacklist student
    public function getBlacklistStudentlistDatatable(Request $request)
    {
        // dd("inside blacklist controller");
        // dd($request->hasOrganization);
        if (request()->ajax()) {
            $oid = $request->oid;

            // $dormid = $request->dormid;

            $hasOrganizaton = $request->hasOrganization;

            $userId = Auth::id();

            if ($oid != '' && !is_null($hasOrganizaton)) {

                $data = DB::table('students')
                    ->join('class_student', 'class_student.student_id', '=', 'students.id')
                    ->join('class_organization as co', 'co.id', '=', 'class_student.organclass_id')
                    ->join('classes', 'classes.id', '=', 'co.class_id')
                    ->join('dorms', 'dorms.id', '=', 'class_student.dorm_id')
                    ->where('dorms.organization_id', $oid)
                    ->where('class_student.blacklist', '=', 1)
                    ->whereNotNull('class_student.dorm_id')
                    ->select('class_student.id', 'students.nama as studentName', 'classes.nama as className', 'dorms.name as dormName', 'class_student.blacklist as status')
                    ->orderBy('students.nama')
                    ->get();


                $table = Datatables::of($data);

                $table->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-danger"> Blocked </span></div>';

                        return $btn;
                    } else {
                        $btn = '<div class="d-flex justify-content-center">';
                        $btn = $btn . '<span class="badge badge-success"> Unblock </span></div>';

                        return $btn;
                    }
                });

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger unblockBtn m-1">Unblock</button></div>';
                    $btn = $btn . '<a href="' . route('dorm.reportPerStudent', $row->id) . '" class="btn btn-primary m-1">Report</a>';
                    return $btn;
                });

                $table->rawColumns(['status', 'action']);
                return $table->make(true);
            }
        }
    }

    public function fetchDorm(Request $request)
    {
        $oid = $request->get('oid');

        $list = DB::table('dorms')
            ->where('dorms.organization_id', $oid)
            ->select()
            ->orderBy('dorms.name')
            ->get();

        return response()->json(['success' => $list]);
    }

    // 拿created at当天的application而已吗
    // 需不需要让user选display approve和pending的呢？
    // for button：
        // 如果是kecemasan， status=0 也要display给guard
    public function getStudentOutingDatatable(Request $request)
    {
        if (request()->ajax()) {
            $oid = $request->oid;
            $hasOrganizaton = $request->hasOrganization;

            $roles = DB::table('organization_roles')
                ->join('organization_user as ou', 'ou.role_id', '=', 'organization_roles.id')
                ->where([
                    ['ou.user_id', Auth::user()->id],
                    ['ou.organization_id', $oid],
                ])
                ->value('organization_roles.nama');


            if ($oid != '' && !is_null($hasOrganizaton)) {
                if (Auth::user()->hasRole('Penjaga')) {
                    $data = DB::table('students')
                    ->join('class_student as cs', 'cs.student_id', '=', 'students.id')
                    ->join('student_outing as so', 'so.class_student_id', '=', 'cs.id')
                    ->join('organization_user_student as ous', 'ous.student_id', '=', 'students.id')
                    ->join('organization_user as ou', 'ou.id', '=', 'ous.organization_user_id')
                    ->join('organization_roles as or', 'or.id', '=', 'ou.role_id')
                    ->where([
                        ['ou.organization_id', $oid],
                        ['ou.user_id', Auth::user()->id],
                    ])
                    ->select('so.id as id', 'students.nama', 'students.parent_tel', 'so.apply_date_time', 'so.out_date_time', 
                    'so.arrive_date_time', 'so.in_date_time', 'so.status as status', 'so.reason')
                    ->orderBy('so.status')
                    ->orderBy('students.nama')
                    ->get();
                }
                else{
                    $data = DB::table('students')
                    ->join('class_student as cs', 'cs.student_id', '=', 'students.id')
                    ->join('student_outing as so', 'so.class_student_id', '=', 'cs.id')
                    ->join('organization_user_student as ous', 'ous.student_id', '=', 'students.id')
                    ->join('organization_user as ou', 'ou.id', '=', 'ous.organization_user_id')
                    ->join('organization_roles as or', 'or.id', '=', 'ou.role_id')
                    ->join('classifications', 'classifications.id', '=', 'so.classification_id')
                    ->where([
                        ['ou.organization_id', $oid],
                    ])
                    ->select('so.id as id', 'students.nama', 'students.parent_tel', 'so.apply_date_time', 'so.out_date_time', 
                    'so.arrive_date_time', 'so.in_date_time', 'so.status as status', 'so.reason', 'classifications.name as catname')
                    ->orderBy('so.status')
                    ->orderBy('students.nama')
                    ->get();
                }
            }

            if(isset($data))
            {
                $table = Datatables::of($data);

                $table->addColumn('action', function ($row) {

                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    if($row->status == 0){  //havent approved
                        if(Auth::user()->hasRole('Penjaga'))
                        {
                            if($row->apply_date_time > now()){
                                $btn = $btn . '<a href="' . route('dorm.edit', $row->id) . '" class="btn btn-primary m-1">Edit</a>';
                            }
                            $btn = $btn . '<button id="' . $row->id . '" data-token="' . $token . '" class="btn btn-danger m-1">Buang</button></div>';
                        }
                        elseif(Auth::user()->hasRole('Guru') && strtoupper($row->catname) == ("BALIK KECEMASAN"))
                        {
                            if($row->out_date_time == NULL && $row->in_date_time == NULL && $row->arrive_date_time == NULL)
                            {
                                $btn = $btn . '<a href="' . route('dorm.updateOutTime', $row->id) . '" class="btn btn-primary m-1">Keluar</a>';
                            }
                        }
                        elseif(Auth::user()->hasRole('Superadmin') || Auth::user()->hasRole('Pentadbir') || Auth::user()->hasRole('Guru'))
                        {
                            $btn = $btn . '<a href="' . route('dorm.updateApprove', $row->id) . '" class="btn btn-primary m-1">Approve</a>';
                            $btn = $btn . '<a href="' . route('dorm.updateTolak', $row->id) . '" class="btn btn-danger m-1">Tolak</a>';
                        }
                    }
                    elseif($row->status == 1){ //approved
                        if(Auth::user()->hasRole('Penjaga'))
                        {
                            if($row->in_date_time == NULL && $row->arrive_date_time == NULL && $row->out_date_time != NULL)
                            {
                                $btn = $btn . '<a href="' . route('dorm.updateArriveTime', $row->id) . '" class="btn btn-primary m-1">Sampai</a>';
                            }
                        }
                        elseif(Auth::user()->hasRole('Guru'))
                        {
                            if($row->out_date_time == NULL && $row->in_date_time == NULL && $row->arrive_date_time == NULL)
                            {
                                $btn = $btn . '<a href="' . route('dorm.updateOutTime', $row->id) . '" class="btn btn-primary m-1">Keluar</a>';
                            }

                            if($row->in_date_time == NULL && $row->arrive_date_time != NULL && $row->out_date_time != NULL)
                            {
                                $btn = $btn . '<a href="' . route('dorm.updateInTime', $row->id) . '" class="btn btn-primary m-1">Masuk</a>';
                            }
                        }
                    }
                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }
        }
    }

    //application functions
    public function updateApprove($id)
    {
        $studentouting = DB::table('student_outing')
        ->where('student_outing.id', $id)
        ->update([
            'student_outing.status' => 1,
        ]);

        return redirect('/dorm')->with('success', 'Permintaan pelajar telah disahkan');
    }

    public function updateTolak($id)
    {
        $studentouting = DB::table('student_outing')
        ->where('student_outing.id', $id)
        ->update([
            'student_outing.status' => 2,
        ]);

        return redirect('/dorm')->with('success', 'Permintaan pelajar ditolak');
    }

    public function updateOutTime($id)
    {
        $catname = DB::table('student_outing')
        ->join('classifications', 'classifications.id', '=', 'student_outing.classification_id')
        ->where([
            ['student_outing.id', $id],
        ])
        ->value('classifications.name');

        $outinglimit = (int)DB::table('student_outing')
        ->join('class_student as cs', 'cs.id', '=', 'student_outing.class_student_id')
        ->where([
            ['student_outing.id', $id],
        ])
        ->value('cs.outing_limit');

        if(strtoupper($catname) == "BALIK KECEMASAN")
        {
            DB::table('student_outing')
            ->join('class_student as cs', 'cs.id', '=', 'student_outing.class_student_id')
            ->where([
                ['student_outing.id', $id],
            ])
            ->update([
                'student_outing.status' => 1,
                'student_outing.out_date_time' => now()->toDateTimeString(),
                'cs.outing_status' => 1,
            ]);
        }
        else if(strtoupper($catname) == "BALIK KHAS")
        {
            DB::table('student_outing')
            ->join('class_student as cs', 'cs.id', '=', 'student_outing.class_student_id')
            ->where([
                ['student_outing.id', $id],
                ['student_outing.status', 1],
            ])
            ->update([
                'student_outing.out_date_time' => now()->toDateTimeString(),
                'cs.outing_status' => 1,
                'cs.outing_limit' => $outinglimit + 1,
            ]);
        }
        else
        {
            DB::table('student_outing')
            ->join('class_student as cs', 'cs.id', '=', 'student_outing.class_student_id')
            ->where([
                ['student_outing.id', $id],
                ['student_outing.status', 1],
            ])
            ->update([
                'student_outing.out_date_time' => now()->toDateTimeString(),
                'cs.outing_status' => 1,
            ]);
        }
        return redirect('/dorm')->with('success', 'Tarikh dan masa keluar telah dicatatkan');
    }

    public function updateInTime($id)
    {
        DB::table('student_outing')
        ->join('class_student as cs', 'cs.id', '=', 'student_outing.class_student_id')
        ->where([
            ['student_outing.id', $id],
            ['student_outing.status', 1],
            ['cs.outing_status', 1],
        ])
        ->update([
            'student_outing.in_date_time' => now()->toDateTimeString(),
            'cs.outing_status' => 0,
        ]);

        return redirect('/dorm')->with('success', 'Tarikh dan masa masuk telah dicatatkan');
    }

    public function updateArriveTime($id)
    {
        DB::table('student_outing')
        ->join('class_student as cs', 'cs.id', '=', 'student_outing.class_student_id')
        ->where([
            ['student_outing.id', $id],
            ['student_outing.status', 1],
            ['cs.outing_status', 1],
        ])
        ->update(['student_outing.arrive_date_time' => now()->toDateTimeString()]);

        return redirect('/dorm')->with('success', 'Tarikh dan masa sampai destinasi telah dicatatkan');
    }

    public function updateOutingTime($id)
    {
        // $outing = Outing::findOrFail($id);
        // $name = $request->input('stud_name');
        // DB::update('update student set name = ? where id = ?',[$name,$id]);
        // echo "Record updated successfully.<br/>";
        // echo '<a href = "/edit-records">Click Here</a> to go back.';

        // $outing->update(array('start_date_time' => new DateTime()));
        // return redirect('/asrama')->with('success', 'Data is successfully updated');
    }
}
