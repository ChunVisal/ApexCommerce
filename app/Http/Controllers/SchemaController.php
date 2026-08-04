<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SchemaController extends Controller
{
    public function index()
    {
        // Get all tables in database
        $tables = DB::select('SHOW TABLES');

        $schema = [];

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $columns = Schema::getColumnListing($tableName);
            $columnTypes = [];

            foreach ($columns as $column) {
                $type = DB::select("SHOW COLUMNS FROM $tableName WHERE Field = ?", [$column]);
                $columnTypes[$column] = $type[0]->Type ?? 'unknown';
            }

            $schema[$tableName] = $columnTypes;
        }

        return view('layouts.schema', compact('schema'));
    }
}
