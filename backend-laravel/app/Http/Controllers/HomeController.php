<?php

namespace App\Http\Controllers;

use App\UFiles;
use Illuminate\Http\Request;
use App\PDFConverter;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $converter;
    public function __construct(PDFConverter $converter)
    {
        $this->middleware('auth');
        $this->converter = $converter;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = UFiles::where(['user_id' => Auth::id()])->orderByDesc('created_at')->get();
        return view('home',[
            'extensions' => ".".implode(", .",$this->converter->getConvertExtensions()), //доступные расширения файлов
            'myFiles' => $files->toArray(), //список уже загруженных файлов
        ]);
    }
    public function convertDialog($id){
        $file = UFiles::getFileEntry($id);
        if($file){
            return view('convert-dialog',['file' => $file]);
        }else{
            throw new \Exception("File id doesn't exists");
        }
    }
}
