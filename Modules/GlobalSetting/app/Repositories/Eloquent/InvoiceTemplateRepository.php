<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Illuminate\Support\Collection;
use Modules\GlobalSetting\app\Repositories\Contracts\InvoiceTemplateInterface;
use Modules\GlobalSetting\app\Models\InvoiceTemplate;
use Illuminate\Support\Carbon;

class InvoiceTemplateRepository implements InvoiceTemplateInterface
{
    public function getAllTemplates(array $filters = []): Collection
    {
        return InvoiceTemplate::query()
            ->select('id', 'invoice_title', 'invoice_type', 'template_content', 'is_default')
            ->whereNull('deleted_at')
            ->orderBy($filters['sort_by'] ?? 'id', $filters['order_by'] ?? 'desc')
            ->get();
    }

    public function getTemplateById(int $id): array
    {
        $template = InvoiceTemplate::select('id', 'invoice_title', 'invoice_type', 'template_content', 'is_default')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();

        return $template->toArray();
    }

    public function createTemplate(array $data): array
    {
        $template = InvoiceTemplate::create([
            'invoice_title' => $data['invoice_title'],
            'invoice_type' => $data['invoice_type'],
            'template_content' => $data['template_content'],
        ]);

        return $template->toArray();
    }

    public function updateTemplate(int $id, array $data): array
    {
        $template = InvoiceTemplate::findOrFail($id);
        $template->update([
            'invoice_title' => $data['invoice_title'],
            'invoice_type' => $data['invoice_type'],
            'template_content' => $data['template_content'],
        ]);

        return $template->toArray();
    }

    public function deleteTemplate(int $id): bool
    {
        $template = InvoiceTemplate::findOrFail($id);
        $template->deleted_at = Carbon::now();
        return $template->save();
    }

    public function setDefaultTemplate(int $id): bool
    {
        InvoiceTemplate::where('is_default', 1)->update(['is_default' => 0]);
        
        $template = InvoiceTemplate::findOrFail($id);
        $template->is_default = 1;
        return $template->save();
    }

    public function searchTemplates(string $searchTerm): Collection
    {
        return InvoiceTemplate::query()
            ->select('id', 'invoice_title', 'invoice_type', 'template_content', 'is_default')
            ->whereNull('deleted_at')
            ->where(function($query) use ($searchTerm) {
                $query->where('invoice_title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('template_content', 'like', '%' . $searchTerm . '%');
            })
            ->get();
    }
}