<?php

namespace App\Http\Controllers;

use App;
use Auth;
use Redirect;
use App\Models\FAQ;
use App\Models\FAQCategory;
use App\Models\Inquiry;
use App\Models\SystemNotify;
use App\Models\SystemNotifyColor;
use Illuminate\Http\Request;

class CMSController extends Controller
{
    public function faq() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $tbl = new FAQCategory();
        $categories = $tbl->getAll(App::getLocale());

        return view('cms.faq.list', [
            'categories'    => $categories,
        ]);
    }

    public function ajax_faqSearch(Request $request) {
        $params = $request->all();

        $tbl = new FAQ();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getFAQInfo(Request $request) {
        $id = $request->get('id');

        $tbl = new FAQ();
        $ret = $tbl->getRecordById($id);

        return response()->json($ret);
    }

    public function ajax_addFAQ(Request $request) {
        $ret = $this->validate($request, [
            'category'  => 'required',
			'lang'		=> 'required',
            'question'  => 'required',
            'answer'    => 'required',
            'status'    => 'required',
        ]);

        $params = $request->all();

        $tbl = new FAQ();
        $ret = $tbl->insertRecord($params);

        return response()->json($ret);
    }

    public function ajax_editFAQ(Request $request) {
        $ret = $this->validate($request, [
            'id'        => 'required',
            'category'  => 'required',
			'lang'		=> 'required',
            'question'  => 'required',
            'answer'    => 'required',
            'status'    => 'required',
        ]);

        $params = $request->all();

        $tbl = new FAQ();
        $ret = $tbl->updateRecord($params);

        return response()->json($ret);
    }

    public function ajax_deleteFAQ(Request $request) {
        $id = $request->get('id');

        $tbl = new FAQ();
        $ret = $tbl->deleteRecord($id);

        return response()->json($ret);
    }

    public function faq_categories() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('cms.faq.categories');
    }

    public function ajax_faqCategorySearch(Request $request) {
        $params = $request->all();

        $tbl = new FAQCategory();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getFAQCategoryInfo(Request $request) {
        $id = $request->get('id');

        $tbl = new FAQCategory();
        $ret = $tbl->getRecordById($id);

        return response()->json($ret);
    }

	public function ajax_getAllCategories(Request $request) {
		$lang = $request->get('lang');

		$tbl = new FAQCategory();
		$ret = $tbl->getAll($lang);

		return response()->json($ret);
	}

    public function ajax_addFAQCategory(Request $request) {
        $ret = $this->validate($request, [
			'lang'		=> 'required',
			'name'		=> 'required',
        ]);

        $params = $request->all();

        $tbl = new FAQCategory();
        $ret = $tbl->insertRecord($params);

        return response()->json($ret);
    }

    public function ajax_editFAQCategory(Request $request) {
        $ret = $this->validate($request, [
            'id'        => 'required',
            'lang'		=> 'required',
			'name'		=> 'required',
        ]);

        $params = $request->all();

        $tbl = new FAQCategory();
        $ret = $tbl->updateRecord($params);

        return response()->json($ret);
    }

    public function ajax_deleteFAQCategory(Request $request) {
        $id = $request->get('id');

        $tbl = new FAQCategory();
        $ret = $tbl->deleteRecord($id);

        return response()->json($ret);
    }

    public function inquiry() {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        return view('cms.inquiry.list');
    }

    public function ajax_inquirySearch(Request $request) {
        $params = $request->all();

        $tbl = new Inquiry();
        $ret = $tbl->getForDatatable($params);

        return response()->json($ret);
    }

    public function ajax_getInquiryInfo(Request $request) {
        $id = $request->get('id');

        $tbl = new Inquiry();
        $ret = $tbl->getRecordById($id);

        return response()->json($ret);
    }

    public function ajax_editInquiry(Request $request) {
        $ret = $this->validate($request, [
            'id'        => 'required',
            'status'    => 'required',
        ]);

        $params = $request->all();

        $tbl = new Inquiry();
        $ret = $tbl->updateRecord($params);

        return response()->json($ret);
    }

    public function notify(Request $request) {
        $user = Auth::user();
        if ($user->role != USER_ROLE_ADMIN && $user->role != USER_ROLE_CASINO) {
            return redirect()->back();
        }

        $systemNotifyModel = new SystemNotify();
        $systemNotifyColorModel = new SystemNotifyColor();

        $result = $systemNotifyModel->getAll();
        $colorResult = $systemNotifyColorModel->getAll();

        $content = array();
        foreach ($result as $key => $data) {
            $content[$data->lang] = $data->content;
        }
        $content1 = '';
        $content2 = '';
        $color1 = '';
        $color2 = '';
        $color3 = '';

        if (count($colorResult) > 0) {
            $color1 = $colorResult[0]->color;
        }
        if (count($colorResult) > 1) {
            $color2 = $colorResult[1]->color;
        }
        if (count($colorResult) > 2) {
            $color3 = $colorResult[2]->color;
        }

        return view('cms.notify.list', [
            'content'  => $content,
            'lang'      => 'jp',
            'color1'    => $color1,
            'color2'    => $color2,
            'color3'    => $color3,
        ]);
    }

    public function notify_modify(Request $request) {
        $param = $request->all();

        $systemNotifyModel = new SystemNotify();
        $systemNotifyModel->updateRecord($param['content'], $param['lang']);

        return Redirect::back()->with('message', trans('notify.message'));
    }

    public function notify_modifycolor(Request $request) {
        $param = $request->all();

        $systemNotifyColorModel = new SystemNotifyColor();
        $systemNotifyColorModel->updateRecord($param);

        return Redirect::back()->with('message_color', trans('notify.message'));
    }
}
