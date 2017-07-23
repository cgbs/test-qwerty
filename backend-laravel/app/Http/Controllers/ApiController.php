<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\PDFConverter;
use App\UFiles;

class ApiController extends Controller
{
    private $converter;
    private $store_path;
    private $income_folder;
    private $pdf_folder;
    private $api_user_id = 0; //first user in table is API user
    public function __construct(PDFConverter $converter)
    {
        $this->converter = $converter;
        $this->store_path = 'api-files';
        $this->income_folder = 'income';
        $this->pdf_folder = 'pdf/';
        $this->api_user_id = User::where(['email' => 'api'])->first()->id;
    }
    private function getIncomePath(){   //путь сохранения оригинальных файлов
        return $this->store_path .'/'. $this->income_folder;
    }
    private function getPdfPath(){      //путь сохранения pdf файлов
        return $this->store_path .'/'. $this->pdf_folder;
    }
    public function getFileList(){      //выдать список загруженных файлов
        $files = UFiles::select('id','original_filename as name','created_at','converted_filename')->where(['user_id' => $this->api_user_id])->orderByDesc('created_at')->get()->toArray();
        for ($i=0; $i<count($files); $i++){
            $files[$i]['converted'] = !empty($files[$i]['converted_filename']);
            unset($files[$i]['converted_filename']);
        }
        return $files;
    }
    public function getExtensions(){    //список доддерживаемых разрешений
        return $this->converter->getConvertExtensions();
    }
    public function uploadFiles(Request $request,$convert){  //загрузка файлов
        $convert = ($convert  === 'true') ? true : false;
        if($request->hasFile('docs')){
            $docs = $request->file('docs');
            foreach($docs as $doc){
                if(in_array($doc->getClientOriginalExtension(),$this->converter->getConvertExtensions())) { //проверка на поддерживаемые разрешения
                    $store_name = $doc->store($this->getIncomePath());
                    $new_enty = UFiles::create([
                        'user_id' => $this->api_user_id,
                        'original_filename' => $doc->getClientOriginalName(),
                        'server_filename' => $store_name,
                    ]);
                    if($convert){   //вызываем конвертацию если нужно
                        $this->convertFile($new_enty->id,'true','false');
                    }
                }
            }
        }
    }
    public function convertFile($id,$protect,$rastr){
        $protect = ($protect  === 'true') ? true : false;
        $rastr = ($rastr  === 'true') ? true : false;
        $file_entry = UFiles::where(['id' => $id, 'user_id' => $this->api_user_id])->first();
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
        if($rastr){
            $pdf_name = $this->converter->resterizePDF(   //растеризация
                $this->getStorageFullPath(
                    $this->getPdfPath().$pdf_name   //это получение абсолютного пути
                )
            );
        }
        if($protect){
            $pdf_name = $this->converter->copyProtection(   //защитить от записи
                $this->getStorageFullPath(
                    $this->getPdfPath().$pdf_name
                )
            );
        }
        $file_entry->converted_filename = $this->getPdfPath().$pdf_name;
        $file_entry->save();
    }

    public function getFileOriginal($id){
        $file_entry = UFiles::where(['id' => $id, 'user_id' => $this->api_user_id])->first();
        return $this->downloadFromStorage($file_entry->server_filename,$file_entry->original_filename);
    }
    public function getFilePDF($id){
        $file_entry = UFiles::where(['id' => $id, 'user_id' => $this->api_user_id])->first();
        if(!empty($file_entry->converted_filename)){
            return $this->downloadFromStorage($file_entry->converted_filename,$this->replace_extension($file_entry->original_filename,'pdf'));
        }
    }
    public function deleteFile($id){ //Удаляет файлы и соотвествующую запись в БД
        $file_entry = UFiles::where(['id' => $id, 'user_id' => $this->api_user_id])->first();
        if(!empty($file_entry->server_filename)){   //original
            Storage::delete($file_entry->server_filename);
        }
        if(!empty($file_entry->converted_filename)){   //pdf
            Storage::delete($file_entry->converted_filename);
        }
        $file_entry->delete();
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
    private function getStorageFullPath($path){
        return Storage::disk('local')->getDriver()->getAdapter()->applyPathPrefix($path);
    }
    private function replace_extension($filename, $new_extension) {     //возращает имя файла с другим расширением
        return pathinfo($filename, PATHINFO_FILENAME).'.'.$new_extension;
    }
}
