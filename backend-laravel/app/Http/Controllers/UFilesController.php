<?php

namespace App\Http\Controllers;

use App\UFiles;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PDFConverter;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UFilesController extends Controller
{
    private $converter;
    private $store_path;
    private $income_folder;
    private $pdf_folder;
    public function __construct(PDFConverter $converter)
    {
        $this->middleware('auth');
        $this->converter = $converter;
        $this->store_path = 'user-files/';
        $this->income_folder = '/income';
        $this->pdf_folder = '/pdf/';
    }
    private function getIncomePath(){
        return $this->store_path . Auth::id() . $this->income_folder;
    }
    private function getPdfPath(){
        return $this->store_path . Auth::id() . $this->pdf_folder;
    }
    public function upload(Request $request){   //загрузка файла на сервер
        if($request->hasFile('docs')){
            $docs = $request->file('docs');
            foreach($docs as $doc){
                if(in_array($doc->getClientOriginalExtension(),$this->converter->getConvertExtensions())){ //проверка на поддерживаемые разрешения
                    $store_name = $doc->store($this->getIncomePath());
                    $pdf_name = null;
                    if($request->input('submit-convert')){  //конвертируем сразу же если нужно
                        $pdf_name = $this->converter->convertToPDF(
                            $this->getStorageFullPath($store_name),
                            $this->getStorageFullPath($this->getPdfPath())
                        );
                        $pdf_name = $this->converter->copyProtection(   //защитить от записи
                            $this->getStorageFullPath(
                                $this->getPdfPath().$pdf_name   //это получение абсолютного пути
                            )
                        );
                    }
                    //создание записи о файле
                    if(!empty($pdf_name)) $pdf_name = $this->getPdfPath().$pdf_name;
                    UFiles::create([
                        'user_id' => Auth::id(),
                        'original_filename' => $doc->getClientOriginalName(),
                        'server_filename' => $store_name,
                        'converted_filename' => $pdf_name
                    ]);
                }else{
                    throw new \Exception('one of files has incorrect format');
                }
            }
        }
        return redirect('/home');
    }
    private function getStorageFullPath($path){
        return Storage::disk('local')->getDriver()->getAdapter()->applyPathPrefix($path);
    }
    private function downloadFromStorage($path,$name){  //создает загрузку из стораджа
        if(Storage::exists($path)){
            return response()->download(
                $this->getStorageFullPath($path),
                $name,
                [
                    'Content-Type' => Storage::mimeType($path)
                ]
            );
        }
    }
    private function replace_extension($filename, $new_extension) {     //возращает имя файла с другим расширением
        return pathinfo($filename, PATHINFO_FILENAME).'.'.$new_extension;
    }
    public function getOriginal($id){   //возвращает оригинал в каачестве загрузки
        $file_entry = UFiles::getFileEntry($id);
        return $this->downloadFromStorage($file_entry->server_filename,$file_entry->original_filename);
    }
    public function getPdf($id){    //возвращает в качестве загрузки конвертиованный файл
        $file_entry = UFiles::getFileEntry($id);
        if(!empty($file_entry->converted_filename)){
            if(Storage::exists($file_entry->converted_filename)){
                return $this->downloadFromStorage($file_entry->converted_filename,$this->replace_extension($file_entry->original_filename,'pdf'));
            }else{
                throw new FileNotFoundException("File not found on the server");
            }
        }else{
            return redirect('/home');
        }
    }
    public function delete($id){    //Удаляет файлы и соотвествующую запись в БД
        $file_entry = UFiles::getFileEntry($id);
        if(!empty($file_entry->server_filename)){   //original
            Storage::delete($file_entry->server_filename);
        }
        if(!empty($file_entry->converted_filename)){   //pdf
            Storage::delete($file_entry->converted_filename);
        }
        $file_entry->delete();
        return redirect('/home');
    }
    public function convertExists($id){ //конвертация уже загруженного файла
        $file_entry = UFiles::getFileEntry($id);
        if($file_entry){
            if(!empty($file_entry->converted_filename)){
                Storage::delete($file_entry->converted_filename); //удаляем старый файл
            }
        }
        //конвертируем по новой
        $pdf_name = $this->converter->convertToPDF(
            $this->getStorageFullPath($file_entry->server_filename),
            $this->getStorageFullPath($this->getPdfPath())
        );
        if(!empty(request()->input('convert-rastr'))){
            $pdf_name = $this->converter->resterizePDF(   //растеризация
                $this->getStorageFullPath(
                    $this->getPdfPath().$pdf_name   //это получение абсолютного пути
                )
            );
        }
        if(!empty(request()->input('convert-protect'))){
            $pdf_name = $this->converter->copyProtection(   //защитить от записи
                $this->getStorageFullPath(
                    $this->getPdfPath().$pdf_name
                )
            );
        }
        $file_entry->converted_filename = $this->getPdfPath().$pdf_name;
        $file_entry->save();
        return redirect('/home');
    }
}