<?php

/**
 * A class to generate mif/mid files 
 * @author yangaimin
 *
 */

class MifBuilder {
    
    public $filename = "output";
    public $columns = array();
    public $data = array();
    
    public $path = "/home/data/map/unshot";
    public $version = "450";
    public $charset = "WindowsSimpChinese";
    public $delimiter = ",";
    public $coordsys = "Earth Projection 1, 104";
    public $coordKey = "coords";
    
    public function build() {
        $this->buildMid();
        $this->buildMif();
        $this->buildZip();
    }
    
    private function buildMid() {
        if($this->data && count($this->data)>0) {
            $content = "";
            foreach($this->data as $rec) {
                unset($rec[$this->coordKey]);
                $line = '"' . join('"' . $this->delimiter . '"', $rec) . "\"\n";
                $content .= $line;
            }
            file_put_contents($this->path . "/" . $this->filename . ".mid", $content);
        } else {
            file_put_contents($this->path . "/" . $this->filename . ".mid", "");
        }
    }
    
    private function buildMif() {
        if($this->data && count($this->data)>0) {
            if(empty($this->columns)) {
                $rec = $this->data[0];
                $this->columns = array_keys($rec);
                unset($this->columns[$this->coordKey]);
            }
        
            $content = "Version {$this->version}\n";
            $content .= 'Charset "' . $this->charset . '"' . "\n";
            $content .= 'Delimiter "' . $this->delimiter . '"' . "\n";
            $content .= 'CoordSys ' . $this->coordsys  . "\n";
            $content .= 'Columns ' . count($this->columns) . "\n";
            foreach($this->columns as $column) {
                $content .= "    " . $column . "\n";
            }
            $content .= "Data\n\n";
            
            foreach($this->data as $rec) {
                $str = "Pline MULTIPLE 1\n";
                $str .= '  ' . count(explode(",", $rec[$this->coordKey])) . "\n";
                $str .= str_replace(",", "\n", $rec[$this->coordKey]) . "\n";
                $str .= "    Pen (1,2,0)\n";
                $content .= $str;
                unset($str);
            }
            
            file_put_contents($this->path . "/" . $this->filename . ".mif", $content);
        } else {
            file_put_contents($this->path . "/" . $this->filename . ".mif", "");
        }
    }
    
    private function buildZip() {
        $zip = new ZipArchive();
        $filename = $this->path . "/" . $this->filename . ".zip";
        if (file_exists($filename)) {
            @unlink($filename);
        }
        
        if ($zip->open($filename, ZIPARCHIVE::CREATE) !== true) {
            echo "cannot open <$filename>\n";
        }
        
        if(file_exists($this->path . "/" . $this->filename . ".mid")) {
            $zip->addFile($this->path . "/" . $this->filename . ".mid", $this->filename . ".mid");
        }
        
        if(file_exists($this->path . "/" . $this->filename . ".mif")) {
            $zip->addFile($this->path  . "/" . $this->filename . ".mif", $this->filename . ".mif");
        }
        $zip->close();
    }
    
}