<?php
namespace Modules\GlobalSetting\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\GlobalSetting\app\Repositories\Contracts\SocialLinkRepositoryInterface;

class SocialLinkController extends Controller
{
    protected $socialLinkRepo;

    public function __construct(SocialLinkRepositoryInterface $socialLinkRepo)
    {
        $this->socialLinkRepo = $socialLinkRepo;
    }

    public function socialLinks(): View
    {
        return view('globalsetting::social_links.index');
    }

    public function storeSocialLinks(Request $request)
    {
        return $this->socialLinkRepo->store($request);
    }

    public function getSocialLinks(Request $request)
    {
        return $this->socialLinkRepo->getAll($request);
    }

    public function getSocialLink($id)
    {
        return $this->socialLinkRepo->find($id);
    }

    public function deleteSocialLink(Request $request)
    {
        return $this->socialLinkRepo->delete($request);
    }
}
