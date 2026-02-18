<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Models\SubscriptionPackage;
use Modules\GlobalSetting\app\Repositories\Contracts\SubscriptionPackageInterface;
use Illuminate\Support\Facades\Cache;

class SubscriptionPackageRepository implements SubscriptionPackageInterface
{
    public function __construct(protected SubscriptionPackage $model)
    {
    }

    public function index(array $filters = [])
    {
        $query = $this->model->newQuery()->where('status', 1);

        if (isset($filters['subscriptiontype'])) {
            $query->where('subscription_type', $filters['subscriptiontype']);
        }

        $query->orderBy('price', 'asc');

        return $query->get();
    }

    public function store(array $data)
    {
        $last = $this->model->latest('order_by')->first();
        $data['order_by'] = ($last && $last->order_by) ? $last->order_by + 1 : 1;
        $data['is_default'] = ($data['price'] == 0 || $data['price'] == 0.00) ? 1 : 0;

        if ($data['package_term'] == 'yearly') {
            $data['package_duration'] = 1;
        }

        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $package = $this->model->findOrFail($id);

        if ($data['package_term'] === 'yearly') {
            $data['package_duration'] = 1;
        }

        return $package->update($data);
    }

    public function delete(int $id)
    {
        $package = $this->model->findOrFail($id);
        $package->deleted_at = now();
        return $package->save();
    }

    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function getByType(string $type)
    {
        return $this->model->where('subscription_type', $type)
            ->where('status', 1)
            ->get();
    }
}