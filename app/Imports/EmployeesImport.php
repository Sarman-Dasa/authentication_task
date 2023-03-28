<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesImport implements ToModel , WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        Employee::create([
            'first_name'    =>  $row['first_name'],
            'last_name'     =>  $row['last_name'],
            'email'         =>  $row['email'],
            'phone'         =>  $row['phone'],
            'joining_date'  =>  $row['joining_date'],
            'company_id'    =>  $row['company_id'],
            'user_id'       =>  $row['user_id'],
            // 'first_name'    =>  $row[1],
            // 'last_name'     =>  $row[2],
            // 'email'         =>  $row[3],
            // 'phone'         =>  $row[4],
            // 'joining_date'  =>  $row[5],
            // 'company_id'    =>  $row[6],
        ]);
    }
}
