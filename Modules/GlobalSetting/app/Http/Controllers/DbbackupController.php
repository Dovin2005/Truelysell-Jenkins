<?php

namespace Modules\GlobalSetting\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\GlobalSetting\app\Http\Requests\DbBackupRequest;
use Modules\GlobalSetting\app\Models\Dbbackup;
use Modules\GlobalSetting\app\Repositories\Contracts\DbbackupInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DbbackupController extends Controller
{
    protected $dbBackupRepository;

    public function __construct(DbbackupInterface $dbBackupRepository)
    {
        $this->dbBackupRepository = $dbBackupRepository;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $orderBy = $request->order_by ?? 'desc';
            $sortBy = $request->sort_by ?? 'id';
            $search = $request->search ?? null;

            $query = Dbbackup::query()
                ->selectRaw('id, DATE_FORMAT(DATE(created_at),"%d/%m/%Y") as show_date,name, DATE_FORMAT(time(created_at),"%h:%i %p") as show_time')
                ->whereNull('deleted_at');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            }

            $languages = $query->orderBy($sortBy, $orderBy)->get();

            return response()->json([
                'code' => '200',
                'message' => __('Data retrieved successfully.'),
                'data' => $languages,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'code' => '500',
                'message' => __('An error occurred while retrieving languages.'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function backupDatabase(Request $request)
    {
        $backupDir = storage_path('app/public/dbbackups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $fileName = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $filePath = $backupDir . '/' . $fileName;

        $tables = Db::select('SHOW TABLES');
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

        Dbbackup::create(['name' => $fileName]);

        return redirect()->route('admin.db-settings')->with('success', 'Database backed up successfully');
    }

    public function downloadDatabaseBackup($id): BinaryFileResponse
    {
        try {
            $filePath = $this->dbBackupRepository->downloadBackup($id);
            return response()->download($filePath);
        } catch (\Exception $e) {
            abort(404, 'Backup file not found: ' . $e->getMessage());
        }
    }
}