<?php

/*
 * Taken from
 * https://github.com/laravel/framework/blob/5.3/src/Illuminate/Auth/Console/stubs/make/controllers/HomeController.stub
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;
use DB;
use Illuminate\Support\Facades\Cache;


/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index(Request $request)
    {

    // GET THE CLIENT ID AND SECRET FROM THE DATABASE
    // MAKE IT WORK WITH THE LOGIN VIA THE LOGINFORM.VUE



        $logviewer = new LaravelLogViewer();

        $folderFiles = [];
          if ($request->input('l')) {
            $logviewer->setFile(Crypt::decrypt($request->input('l')));
        }

        $data = [
            'logs' => $logviewer->all(),
            'folders' => $logviewer->getFolders(),
            'current_folder' => $logviewer->getFolderName(),
            'folder_files' => $folderFiles,
            'files' => $logviewer->getFiles(true),
            'current_file' => $logviewer->getFileName(),
            'standardFormat' => true,
        ];



        if (is_array($data['logs'])) {
            $firstLog = reset($data['logs']);
            if (!$firstLog['context'] && !$firstLog['level']) {
                $data['standardFormat'] = false;
            }
        }

        return view('home',$data);
    }
}
