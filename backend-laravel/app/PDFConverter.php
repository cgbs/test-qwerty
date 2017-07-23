<?php

namespace App;
use App;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PDFConverter
{
    private $convertExtensions = ['doc','docx','xls','xlsx','ppt','pptx'];
    //private $convertCommand = '"C:\Program Files (x86)\LibreOffice 5\program\soffice.bin" --headless --convert-to pdf {source} --outdir "{dest}"'; //'libreoffice';
	private $convertCommand = 'libreoffice --headless --convert-to pdf {source} --outdir "{dest}"'; //'libreoffice';
    private $copyProtectionCommand = 'pdftk "{source}" output "{dest}" owner_pw "uberpassword"';
    private $rasterizeCommand = 'convert -density 500 +antialias "{source}" "{dest}"';
    public function getConvertExtensions(){ //возвращает список доступных разширений
        return $this->convertExtensions;
    }
    private function runProcess($cmd){  //обертка для запуска консольных комманд
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
    public function convertToPDF($source,$dest_dir){    //конвертация через libreoffice
        $command = $this->convertCommand;
        $command = str_replace('{source}',$source,$command);
        $command = str_replace('{dest}',$dest_dir,$command);
        $this->runProcess($command);
        return pathinfo(basename($source), PATHINFO_FILENAME).'.pdf';
    }
    public function copyProtection($source){    //защита от копирования и печати через pdftk
        $command = $this->copyProtectionCommand;
        $new_name = $source.'-protected';
        $command = str_replace('{source}',$source,$command);
        $command = str_replace('{dest}',$new_name,$command);
        $this->runProcess($command);
        unlink($source);
        return basename($new_name);
    }
    public function resterizePDF($source){    //полная растеризация через convert Image Magick
        $command = $this->rasterizeCommand;
        $new_name = $source.'-rastr';
        $command = str_replace('{source}',$source,$command);
        $command = str_replace('{dest}',$new_name,$command);
        $this->runProcess($command);
        unlink($source);
        return basename($new_name);
    }
}
