<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Repositories\Contracts\DbbackupInterface;
use Modules\GlobalSetting\app\Models\Dbbackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class DbbackupRepository implements DbbackupInterface
{
    public function index(array $filters)
    {
        $query = Dbbackup::query()
            ->selectRaw('id, DATE_FORMAT(DATE(created_at),"%d/%m/%Y") as show_date, name, DATE_FORMAT(time(created_at),"%h:%i %p") as show_time')
            ->whereNull('deleted_at');

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        $orderBy = $filters['order_by'] ?? 'desc';
        $sortBy = $filters['sort_by'] ?? 'id';

        return $query->orderBy($sortBy, $orderBy)->get();
    }

    public function createBackup()
    {
        $backupDir = storage_path('app/public/dbbackups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $fileName = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupDir . '/' . $fileName;

        $tables = DB::select('SHOW TABLES');
        $database = config('database.connections.mysql.database');
        $key = "Tables_in_$database";

        $sqlDump = "";

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $create = DB::select("SHOW CREATE TABLE `$tableName`")[0]->{'Create Table'};
            $sqlDump .= "DROP TABLE IF EXISTS `$tableName`;\n$create;\n\n";

            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($v) {
                    return is_null($v) ? 'NULL' : "'" . str_replace("'", "''", $v) . "'";
                }, (array) $row);
                $sqlDump .= "INSERT INTO `$tableName` VALUES(" . implode(',', $values) . ");\n";
            }

            $sqlDump .= "\n\n";
        }

        file_put_contents($filePath, $sqlDump);

        return Dbbackup::create(['name' => $fileName]);
    }

    public function downloadBackup(int $id)
    {
        $backup = Dbbackup::findOrFail($id);
        $backupPath = storage_path('app/public/dbbackups/' . $backup->name);

        if (!file_exists($backupPath)) {
            throw new Exception('Backup file not found');
        }

        return $backupPath;
    }
}