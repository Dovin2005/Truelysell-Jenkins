<?php

namespace Modules\Leads\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Categories\app\Models\Categories;
use Modules\Categories\app\Models\CategoryFormInput;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $user_id
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property array<int, array{id: int, value: mixed}>|null $form_inputs
 * @property string $status
 * @property-read Categories|null $category
 * @property-read Categories|null $subCategory
 * @property-read User|null $user
 * @property-read array<int, array{title: string|null, description: string|null, option: mixed|null}>|null $form_inputs_details
 */
class UserFormInput extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'sub_category_id',
        'form_inputs',
        'status',
    ];

    protected $casts = [
        'form_inputs' => 'array',
    ];

    /**
     * Define a relationship with the Categories model.
     *
     * @return BelongsTo<Categories, UserFormInput>
     */
    public function category(): BelongsTo
    {
        /** @var BelongsTo<Categories, UserFormInput> */
        return $this->belongsTo(Categories::class, 'category_id');
    }

    /**
     * Define a relationship with the sub-category (Categories model).
     *
     * @return BelongsTo<Categories, UserFormInput>
     */
    public function subCategory(): BelongsTo
    {
        /** @var BelongsTo<Categories, UserFormInput> */
        return $this->belongsTo(Categories::class, 'sub_category_id')->whereNotNull('parent_id');
    }

    /**
     * Define a relationship with the User model.
     *
     * @return BelongsTo<User, UserFormInput>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, UserFormInput> */
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor to get detailed form inputs.
     *
     * @return array<int, array{title: string|null, description: string|null, option: mixed|null}>|null
     */
    public function getFormInputsDetailsAttribute(): ?array
    {
        $formInputs = json_decode($this->attributes['form_inputs'], true);

        if (is_array($formInputs)) {
            foreach ($formInputs as &$input) {
                $categoryFormInput = CategoryFormInput::find($input['id']);
                if ($categoryFormInput) {
                    $input['details'] = [
                        'title' => $categoryFormInput->label ?? null,
                        'description' => $categoryFormInput->name ?? null,
                        'option' => $categoryFormInput->options ?? null,
                    ];
                }
            }
        }

        return $formInputs;
    }

    /**
     * Define a relationship with the ProviderFormsInput model.
     *
     * @return HasMany<ProviderFormsInput, UserFormInput>
     */
    public function providerFormsInputs(): HasMany
    {
        /** @var HasMany<ProviderFormsInput, UserFormInput> */
        return $this->hasMany(ProviderFormsInput::class, 'user_form_inputs_id', 'id');
    }
}
