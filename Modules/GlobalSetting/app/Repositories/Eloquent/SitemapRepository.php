<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Repositories\Contracts\SitemapInterface;
use Modules\GlobalSetting\app\Models\SitemapUrl;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Facades\File;

class SitemapRepository implements SitemapInterface
{
    public function index(array $params)
    {
        // For future use if needed
        return view('globalsetting::setting.sitemap_settings');
    }

    public function store(array $data)
    {
        return SitemapUrl::create([
            'url' => $data['url']
        ]);
    }

    public function delete(int $id)
    {
        $sitemapUrl = SitemapUrl::findOrFail($id);

        if (!empty($sitemapUrl->sitemap_path)) {
            $path = public_path($sitemapUrl->sitemap_path);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        return $sitemapUrl->delete();
    }

    public function generate()
    {
        $urls = SitemapUrl::all();
        if ($urls->isEmpty()) {
            return null;
        }

        $sitemap = Sitemap::create();
        foreach ($urls as $url) {
            if ($url->url) {
                $sitemap->add(
                    Url::create($url->url)
                        ->setLastModificationDate(now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.8)
                );
            }
        }

        $sitemapFolder = public_path('sitemaps');
        if (!File::exists($sitemapFolder)) {
            File::makeDirectory($sitemapFolder, 0755, true);
        }

        $relativePath = 'sitemaps/sitemap.xml';
        $fullPath = public_path($relativePath);
        
        // Archive old sitemap if exists
        $latestUrl = SitemapUrl::orderByDesc('id')->first();
        if ($latestUrl && $latestUrl->sitemap_path && File::exists(public_path($latestUrl->sitemap_path))) {
            $newFilename = 'sitemaps/sitemap-' . date('Y-m-d-H-i-s') . '-' . rand(1000, 9999) . '.xml';
            File::move(public_path($latestUrl->sitemap_path), public_path($newFilename));
        }

        $sitemap->writeToFile($fullPath);
        
        if (File::exists($fullPath)) {
            SitemapUrl::latest()->first()->update(['sitemap_path' => $relativePath]);
            return $relativePath;
        }

        return null;
    }

    public function getUrls(array $params)
    {
        $query = SitemapUrl::query();

        if (!empty($params['search'])) {
            $query->where('url', 'like', '%' . $params['search'] . '%');
        }

        return [
            'total' => $query->count(),
            'filtered' => $query->count(),
            'data' => $query->orderBy('id', 'desc')
                ->skip($params['offset'] ?? 0)
                ->take($params['limit'] ?? 10)
                ->get()
                ->map(function ($item) {
                    return [
                        'filePath' => $item->sitemap_path && File::exists(public_path($item->sitemap_path))
                            ? asset($item->sitemap_path)
                            : '',
                        'url' => $item->url,
                        'sitemap_path' => $item->sitemap_path,
                        'id' => $item->id,
                    ];
                })
        ];
    }
}