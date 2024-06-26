<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StudentExport implements FromCollection, ShouldAutoSize, WithHeadings,WithColumnFormatting
{
    /**
    * @return \Illuminate\Support\Collection
    */

    // protected $organId, $kelasId;

    public function __construct($organId, $kelasId,$year)
    {
        $this->organId = $organId;
        $this->kelasId = $kelasId;
        $this->year = $year;
    }

    public function collection()
    {
        set_time_limit(300);
        $query = DB::table('organization_user_student as ous')
        ->join('students', 'students.id', '=', 'ous.student_id')
        ->join('class_student as cs', 'cs.student_id', '=', 'students.id')
        ->join('class_organization as co', 'co.id', '=', 'cs.organclass_id')
        ->join('classes as c', 'c.id', '=', 'co.class_id')
        ->join('organization_user as ou', 'ou.id', 'ous.organization_user_id')
        ->join('users', 'users.id', 'ou.user_id');

        if($this->kelasId != null){
            $liststudents =$query->select('students.nama', 'students.gender',  'users.name', 
             DB::raw("CASE 
                WHEN users.icno IS NOT NULL THEN users.icno
                ELSE REPLACE(users.telno, '+6', '')
                END AS telno"))
            ->where([
                ['co.organization_id', $this->organId],
                ['c.id', $this->kelasId],
                ['cs.status', 1],
                ['ou.role_id', 6],
            ])
             
            ->orderBy('students.nama')
            ->get();
        }
        else {
           // $liststudents = $query->select('students.nama', 'students.gender',  'users.name', DB::raw("REPLACE(users.telno, '+6', '') as telno"),'c.nama as class_name')
            $liststudents =$query->select('students.nama', 'students.gender',  'users.name', 
            DB::raw("CASE 
               WHEN users.icno IS NOT NULL THEN users.icno
               ELSE REPLACE(users.telno, '+6', '')
               END AS telno"),
            'c.nama as class_name')
            ->where([
                ['co.organization_id', $this->organId],
                ['c.nama', 'LIKE', $this->year . '%'], 
                ['cs.status', 1],
                ['ou.role_id', 6],
            ])
            ->orderBy('c.nama')
            ->orderBy('students.nama')
            ->get();
        }
        
       
    

        // dd($liststudents);

        return $liststudents;
    }

    public function headings(): array
    {
        return [
            'nama',
            'jantina',
            'nama_penjaga',
            'no_ic_penjaga',
            'kelas'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER, // Column D represents the telephone numbers (change as needed)
        ];
    }

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function(AfterSheet $event) {
            // Apply custom number format "0" to the telephone numbers column
            $event->sheet->getStyle('D')->getNumberFormat()->setFormatCode('0');
        },
    ];
}
}
