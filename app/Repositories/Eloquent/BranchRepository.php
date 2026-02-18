<?php

namespace App\Repositories\Eloquent;

use App\Models\Branches;
use App\Models\BranchStaffs;
use App\Models\PackageTrx;
use App\Models\User;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BranchRepository implements BranchRepositoryInterface
{
    public function index(Request $request): array
    {
        try {
            $orderBy = $request->order_by ?? 'desc';
            $id = $request->id ?? $request->user_id;

            $data = Branches::where(['created_by' => $id])->orderBy('id', $orderBy)->get()->map(function ($branch) {
                $branch->branch_image = url('storage/branch/' . $branch->branch_image);
                $staff = BranchStaffs::where('branch_id', $branch->id)->pluck('staff_id');
                $branch->staff = $staff->toArray();
                $branch->branch_workingday = explode(',', $branch->branch_workingday);
                $branch->branch_holiday = explode(',', $branch->branch_holiday);;

                return $branch;
            });

            return [
                'code' => 200,
                'message' => __('Branch details retrieved successfully.'),
                'data' => $data,
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => __('An error occurred while retrieving Branch.'),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function addBranch(Request $request): array
    {
        $countries = DB::table('countries')->get();
        $userId = Auth::id();
        $staffs = User::where(['user_details.parent_id' => $userId, 'users.status' => 1])
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
            ->get(['user_details.user_id', 'user_details.first_name', 'user_details.last_name']);

        return [
            'countries' => $countries,
            'staffs' => $staffs
        ];
    }

    public function getCountries(Request $request): array
    {
        try {
            $countries = DB::table('countries')->get(['id', 'name']);

            return [
                'code' => 200,
                'data' => $countries,
                'message' => __('Countries retrieved successfully.')
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'Error! while retrieving countries'
            ];
        }
    }

    public function getStates(Request $request): array
    {
        $countryId = $request->country_id;
        
        $validator = Validator::make($request->all(),[
            'country_id' => [
                'required',
            ],
        ], [
            'country_id.required' => __('Country id is required.'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'code' => 422,
                'message' => $validator->messages()->toArray()
            ];
        }

        try {

            $states = DB::table('states')->where('country_id', $countryId)->get(['id', 'country_id', 'name']);

            return [
                'code' => 200,
                'data' => $states,
                'message' => __('States retrieved successfully.')
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'Error! while retrieving states'
            ];
        }
    }

    public function getCities(Request $request): array
    {
        $stateId = $request->state_id;

        $validator = Validator::make($request->all(),[
            'state_id' => [
                'required',
            ],
        ], [
            'state_id.required' => __('State id is required.'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'code' => 422,
                'message' => $validator->messages()->toArray()
            ];
        }

        try {
            $cities = DB::table('cities')->where('state_id', $stateId)->get(['id', 'state_id', 'name']);

            return [
                'code' => 200,
                'data' => $cities,
                'message' => __('Cities retrieved successfully.')
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'Error! while retrieving cities'
            ];
        }
    }

    public function saveBranch(Request $request): array
    {
        $id = $request->id ?? '';
        $userId = Auth::id() ?? $request->input('created_by');

        try {
            $workingDay = $request->working_day;
            $holiday = $request->holiday;
            $id = $request->id ?? '';

            if (is_array($workingDay)) {
                $workingDay = implode(',', $workingDay);
            }
            if (is_array($holiday)) {
                $holiday = implode(',', $holiday);
            }


            $latitude="";
            $longitude="";

            $address = $request->branch_address;
            $apikey= GlobalSetting::where('key', 'goglemapkey')->value('value');
            $url = "https://maps.google.com/maps/api/geocode/json?address=".urlencode($address)."&key=".config('services.google_maps.key');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $responseJson = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($responseJson);
            $status = $response->status ?? '';

            if ($status == 'OK') {
                $latitude = $response->results[0]->geometry->location->lat;
                $longitude = $response->results[0]->geometry->location->lng;
            }

            $data = [
                'branch_name' => $request->branch_name,
                'branch_email' => $request->email,
                'branch_mobile' => $request->international_phone_number,
                'branch_address' => $request->branch_address,
                'lat' => $latitude,
                'lang' => $longitude,
                'branch_country' => $request->country,
                'branch_state' => $request->state,
                'branch_city' => $request->city,
                'branch_zip' => $request->zip_code,
                'branch_startworkhour' => $request->start_hour,
                'branch_endworkhour' => $request->end_hour,
                'branch_workingday' => $workingDay,
                'branch_holiday' => $holiday,
                'created_by' => Auth::id() ?? $request->created_by,
            ];

            $file = $request->file('branch_image');
            if ($file instanceof \Illuminate\Http\UploadedFile ) {
                $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('branch', $filename, 'public');
                $data['branch_image'] = $filename;
            }

            if ($id) {
                $branch = Branches::where('id', $id)->update($data);

                DB::table('branch_staffs')->where('branch_id', $id)->delete();

                $staffs = $request->staffs ?? '';
                if (is_array($staffs) && !empty($staffs)) {
                    foreach($staffs as $staff) {
                        BranchStaffs::create(['branch_id' => $id, 'staff_id' => $staff]);
                    }
                }

                $isDeletebranchImage = $request->is_delete_branch_image ?? 0;
                if ($isDeletebranchImage == 1) {
                    $branch = Branches::find($id);
                    if ($branch && $branch->branch_image) {
                        Storage::disk('public')->delete('branch/' . $branch->branch_image);
                    }
                    $branch->branch_image = null;
                    $branch->save();
                }

            } else {
                $branch = Branches::create($data);

                $staffs = $request->staffs ?? '';
                if (is_array($staffs) && !empty($staffs)) {
                    foreach($staffs as $staff) {
                        BranchStaffs::create(['branch_id' => $branch->id, 'staff_id' => $staff]);
                    }
                }
            }

            return [
                'code' => 200,
                'message' => __('Branch saved successfully.')
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'Error! while adding branch',
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateBranch(Request $request): array
    {
        $id = $request->id;
        $userId = $request->input('created_by');

        try {
            $branch = Branches::find($id);
            if (!$branch) {
                return ['code' => 404, 'message' => 'Branch not found.'];
            }

            $workingDay = is_array($request->working_day) ? implode(',', $request->working_day) : $request->working_day;
            $holiday = is_array($request->holiday) ? implode(',', $request->holiday) : $request->holiday;

            $latitude = $branch->lat;
            $longitude = $branch->lang;
            if ($request->has('branch_address') && $request->branch_address !== $branch->branch_address) {
                $address = $request->branch_address;
                $apikey = "YOUR_GOOGLE_MAPS_API_KEY";
                $url = "https://maps.google.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . config('services.google_maps.key');

                $responseJson = file_get_contents($url);
                $response = json_decode($responseJson);

                if ($response->status == 'OK') {
                    $latitude = $response->results[0]->geometry->location->lat;
                    $longitude = $response->results[0]->geometry->location->lng;
                }
            }

            $data = [
                'branch_name' => $request->branch_name,
                'branch_email' => $request->email,
                'branch_mobile' => $request->international_phone_number,
                'branch_address' => $request->branch_address,
                'lat' => $latitude,
                'lang' => $longitude,
                'branch_country' => $request->country,
                'branch_state' => $request->state,
                'branch_city' => $request->city,
                'branch_zip' => $request->zip_code,
                'branch_startworkhour' => $request->start_hour,
                'branch_endworkhour' => $request->end_hour,
                'branch_workingday' => $workingDay,
                'branch_holiday' => $holiday,
            ];

            if ($request->hasFile('branch_image')) {
                $file = $request->file('branch_image');
                $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('branch', $filename, 'public');
                $data['branch_image'] = $filename;
            }

            $branch->update($data);

            DB::table('branch_staffs')->where('branch_id', $id)->delete();
            if ($request->has('staffs') && is_array($request->staffs)) {
                foreach ($request->staffs as $staff) {
                    BranchStaffs::create(['branch_id' => $id, 'staff_id' => $staff]);
                }
            }

            return ['code' => 200, 'message' => __('Branch updated successfully.')];
        } catch (\Exception $e) {
            return ['code' => 500, 'message' => 'Error! while updating branch', 'error' => $e->getMessage()];
        }
    }

    public function editBranch(Request $request): array
    {
        $id = $request->id;
        $countries = DB::table('countries')->get();
        $data = Branches::where('id', $id)->first();
        $userId = Auth::id();
        $staffs = User::where(['user_details.parent_id' => $userId, 'users.status' => 1])
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
            ->get(['user_details.user_id', 'user_details.first_name', 'user_details.last_name']);

        if (!empty($data)) {
            $data->branch_image = $data->branch_image ? url('storage/branch/' . $data->branch_image) : null;

            $staff = BranchStaffs::where('branch_id', $id)->pluck('staff_id');
            $data->staff = $staff->toArray();
        }

        return [
            'countries' => $countries,
            'staffs' => $staffs,
            'data' => $data
        ];
    }

    public function deleteBranch(?int $id): array
    {
        try {
            $branch = Branches::where('id', $id)->delete();
            BranchStaffs::where('branch_id', $id)->delete();

            if (!$branch) {
                return [
                    'code' => 404,
                    'message' => __('Branch not found.')
                ];
            }

            return [
                'code' => 200,
                'message' => __('Branch deleted successfully.')
            ];

        } catch (\Exception $e) {
            return [
                'code' => 500,
                'message' => 'Error! while deleting branch'
            ];
        }
    }

    public function checkUnique(Request $request): bool
    {
        $id = $request->input('id');
        $userId = Auth::id() ?? $request->input('user_id');

        $rules = [];

        if ($request->has('branch_name')) {
            $rules['branch_name'] = [
                'required',
                Rule::unique('branches', 'branch_name')->ignore($id)->whereNull('deleted_at')->where('created_by', $userId)
            ];
        }

        $validator = Validator::make($request->only(['branch_name']), $rules);

        if ($validator->fails()) {
            return false;
        }

        return true;
    }

    public function providerBranchLimit(Request $request): array
    {
        $id = Auth::id() ?? $request->user_id;

        $packageTrx = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'regular')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_locations',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $topup = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'topup')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_locations',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        if (!$packageTrx && !$topup) {
            return [
                'code' => 200,
                'success' => false,
                'no_package' => true,
                'message' => 'No Subscription and Topup Found.'
            ];
        }

        $branchCount = Branches::where('created_by', $id)->count();
        $currentDate = now();

        $packageEndDateCount = $packageTrx ? $currentDate->diffInDays(Carbon::parse($packageTrx->end_date), false) : -999;
        $topupEndDateCount   = $topup ? $currentDate->diffInDays(Carbon::parse($topup->end_date), false) : -999;

        $no_of_locations = 0;

        if ($packageEndDateCount > -1) {
            $no_of_locations += $packageTrx->number_of_locations;
        }

        if ($topupEndDateCount > -1) {
            $no_of_locations += $topup->number_of_locations;
        }

        if ($packageEndDateCount <= -1 && $topupEndDateCount <= -1) {
            return [
                'code' => 200,
                'success' => false,
                'sub_end' => true,
                'message' => 'Subscription or Topup Ended.',
            ];
        }

        if ($branchCount >= $no_of_locations) {
            return [
                'code' => 200,
                'success' => false,
                'sub_count_end' => true,
                'message' => 'Subscription or Topup limit Ended.',
            ];
        }

        $redirectUrl = route('provider.addbranch');

        return [
            'code' => 200,
            'success' => true,
            'redirect_url' => $redirectUrl,
            'message' => 'Successfully.',
        ];
    }

    public function providerBranchLimitApi(Request $request): array
    {
        $id = $request->provider_id;

        $packageTrx = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'regular')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_locations',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        $topup = PackageTrx::join('subscription_packages', 'subscription_packages.id', '=', 'package_transactions.package_id')
            ->where('package_transactions.provider_id', $id)
            ->where('package_transactions.status', 1)
            ->where('package_transactions.payment_status', 2)
            ->where('subscription_packages.subscription_type', 'topup')
            ->select(
                'package_transactions.id',
                'package_transactions.provider_id',
                'subscription_packages.number_of_locations',
                'package_transactions.package_id',
                'package_transactions.trx_date',
                'package_transactions.end_date'
            )
            ->orderByDesc('package_transactions.id')
            ->first();

        if (!$packageTrx && !$topup) {
            return [
                'code' => 422,
                'success' => false,
                'no_package' => true,
                'message' => 'No Subscription and Topup Found.'
            ];
        }

        $branchCount = Branches::where('created_by', $id)->count();
        $currentDate = now();

        $packageEndDateCount = $packageTrx ? $currentDate->diffInDays(Carbon::parse($packageTrx->end_date), false) : -999;
        $topupEndDateCount   = $topup ? $currentDate->diffInDays(Carbon::parse($topup->end_date), false) : -999;

        $no_of_locations = 0;

        if ($packageEndDateCount > -1) {
            $no_of_locations += $packageTrx->number_of_locations;
        }

        if ($topupEndDateCount > -1) {
            $no_of_locations += $topup->number_of_locations;
        }

        if ($packageEndDateCount <= -1 && $topupEndDateCount <= -1) {
            return [
                'code' => 422,
                'success' => false,
                'sub_end' => true,
                'message' => 'Subscription or Topup Ended.',
            ];
        }

        if ($branchCount >= $no_of_locations) {
            return [
                'code' => 422,
                'success' => false,
                'sub_count_end' => true,
                'message' => 'Subscription or Topup limit Ended.',
            ];
        }

        return [
            'code' => 200,
            'success' => true,
            'message' => 'Successfully.',
        ];
    }
}