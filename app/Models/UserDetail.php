<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Categories;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use DB;

/**
 * @property string|null $profile_image
 * @property string|null $first_name
 * @property int $category_id
 * 
 */
class UserDetail extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'profile_image',
        'first_name', 
        'last_name', 
        'mobile_number', 
        'gender', 
        'dob', 
        'bio', 
        'address', 
        'lang', 
        'lat', 
        'country', 
        'state', 
        'city', 
        'postal_code', 
        'currency_code', 
        'language',
        'company_image',
        'company_name',
        'company_address',
        'company_website',
        'category_id',
        'subcategory_id',
        'staff_email',
        'staff_phone_number',
        'staff_category',
        'parent_id',
        'status',
        'created_at', 
        'updated_at', 
        'deleted_at'
    ];
    
    public function showname()
    {


        return ucfirst($this->first_name)." ".$this->last_name;
    }
    public function showprofilepic()
    {
        if($this->profile_image!="")
        {
            return url('storage/profile/' . $this->profile_image) ;
        }else{
            return asset('assets/img/profile-default.png');           
        }
    }
    
    public function showaaddress()
    {
        if (empty($this->city) || empty($this->state)) {
            return "";
        }

        $statesPath = public_path() . "/states.json";
        $citiesPath = public_path() . "/cities.json";

        $states = json_decode(file_get_contents($statesPath), true);
        $cities = json_decode(file_get_contents($citiesPath), true);

        // Search by ID
        $cityIndex = array_search($this->city, array_column($cities['cities'], 'id'));
        $stateIndex = array_search($this->state, array_column($states['states'], 'id'));

        if ($cityIndex === false || $stateIndex === false) {
            return "";
        }

        $cityName = $cities['cities'][$cityIndex]['name'];
        $stateName = $states['states'][$stateIndex]['name'];

        return "$cityName, $stateName";
    }

    public function showfulladdress()
    {
        if (empty($this->city) || empty($this->state) || empty($this->country)) {
            return "";
        }

        $pathState = public_path() . "/states.json";
        $pathCity = public_path() . "/cities.json";
        $pathCountry = public_path() . "/countries.json";

        $states = json_decode(file_get_contents($pathState), true);
        $cities = json_decode(file_get_contents($pathCity), true);
        $countries = json_decode(file_get_contents($pathCountry), true);

        $stateIndex = array_search($this->state, array_column($states['states'], 'id'));
        $cityIndex = array_search($this->city, array_column($cities['cities'], 'id'));
        $countryIndex = array_search($this->country, array_column($countries['countries'], 'id'));

        if ($stateIndex === false || $cityIndex === false || $countryIndex === false) {
            return "";
        }

        $stateName = $states['states'][$stateIndex]['name'];
        $cityName = $cities['cities'][$cityIndex]['name'];
        $countryName = $countries['countries'][$countryIndex]['name'];

        return "$cityName, $stateName, $countryName";
    }

    public function showcities()
    {
        if (empty($this->city)) {
            return "";
        }

        $citiesPath = public_path() . "/cities.json";
        $citiesData = json_decode(file_get_contents($citiesPath), true);

        // Search for the city by ID
        $cityIndex = array_search($this->city, array_column($citiesData['cities'], 'id'));

        if ($cityIndex === false) {
            return "";
        }

        return $citiesData['cities'][$cityIndex]['name'];
    }

    /**
     * Generate the file URL for the client image.
     *
     * @param string $file
     * @return string
     */
    public function file(string $file): string
    {
        return url('storage/profile') . '/' . $file;
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id'); // Adjust foreign keys if needed
    }

    public static function getAdminImage()
    {
        $authUserId = Auth::id();
        $profileImage = self::where('user_id', $authUserId)->value('profile_image');
        
        return $profileImage && file_exists(public_path('storage/profile/' . $profileImage)) ? url('storage/profile/' . $profileImage) : url('/assets/img/user-default.jpg');
    }

    public static function getAdminName()
    {
        $id = Auth::id();
        return self::where('user_id', $id)
            ->selectRaw("CONCAT(first_name, ' ', last_name) as full_name")
            ->value('full_name');
    }

    public function branches()
    {
        return $this->hasMany(BranchStaffs::class, 'staff_id', 'user_id');
    }



}
