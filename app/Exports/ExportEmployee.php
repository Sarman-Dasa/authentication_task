<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ExportEmployee implements FromCollection , WithHeadings
{

    public function __construct($start_date,$end_date)
    {
        $this->start_date = $start_date;
        $this->end_date   = $end_date;
    }

    public function collection()
    {
        return Employee::whereBetween('joining_date',[$this->start_date,$this->end_date])->get();
    }

    public function headings() :array
    {
        return ['ID','First Name' , 'Last Name' ,'Email' ,'Phone' ,'Joining Date' ,'Company Id'];
    }


}
