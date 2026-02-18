<?php

namespace Modules\Leads\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\Categories\app\Models\Categories;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProviderFormsInput
 *
 * @property int $user_form_inputs_id
 * @property int $provider_id
 * @property string $status
 * @property float|null $quote
 * @property string|null $start_date
 * @property string|null $description
 * @property string|null $user_status
 *
 * @property-read UserFormInput|null $userFormInput
 * @property-read User|null $provider
 * @property-read Categories|null $category
 * @property-read Categories|null $subCategory
 */
class ProviderFormsInput extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'provider_forms_input';

    protected $fillable = [
        'user_form_inputs_id',
        'provider_id',
        'status',
        'quote',
        'start_date',
        'user_status',
        'description',
    ];

    /**
     * Define a relationship with the UserFormInput model.
     *
     * @return BelongsTo<UserFormInput, ProviderFormsInput>
     */
    public function userFormInput(): BelongsTo
    {
        /** @var BelongsTo<UserFormInput, ProviderFormsInput> */
        return $this->belongsTo(UserFormInput::class, 'user_form_inputs_id');
    }

    /**
     * Define a relationship with the User (provider) model.
     *
     * @return BelongsTo<User, ProviderFormsInput>
     */
    public function provider(): BelongsTo
    {
        /** @var BelongsTo<User, ProviderFormsInput> */
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * Define a relationship with the Categories model.
     *
     * @return BelongsTo<Categories, ProviderFormsInput>
     */
    public function category(): BelongsTo
    {
        /** @var BelongsTo<Categories, ProviderFormsInput> */
        return $this->belongsTo(Categories::class, 'category_id');
    }

    /**
     * Define a relationship with the sub-category (Categories model).
     *
     * @return BelongsTo<Categories, ProviderFormsInput>
     */
    public function subCategory(): BelongsTo
    {
        /** @var BelongsTo<Categories, ProviderFormsInput> */
        return $this->belongsTo(Categories::class, 'sub_category_id')->whereNotNull('parent_id');
    }
}
