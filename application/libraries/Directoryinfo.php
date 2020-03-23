<?php

class Directoryinfo {

    public function readDirectory($Directory, $exclude = array(), $Recursive = true, $level = 0) {
        if (is_dir($Directory) === false) {
            return false;
        }
        $level++;
        try {
            $Resource = opendir($Directory);
            $Found = array();
            $exclude = array_merge($exclude, array(".", "..", ".DS_Store"));
            while (false !== ($Item = readdir($Resource))) {
                if (in_array($Item, $exclude)) {
                    continue;
                }
                if ($Recursive === true && is_dir($Directory . $Item)) {
                    $Directoryname = $Directory . $Item . '/';
                    if ($level == 2 && $Item != "controllers") {
                        continue;
                    }
                    if ($Item == 'controllers') {
                        $Found = $this->readDirectory($Directoryname, true, $level);
                    } else
                        $Found[$Item] = $this->readDirectory($Directoryname, true, $level);
                }else {
                    $filename = $Directory . $Item;
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    if ($ext == "php") {
                        $classname = ucfirst(str_replace('.php', '', $Item));
                        require_once($filename);
                        $reflector = new ReflectionClass($classname);
                        $methodNames = array();
                        $lowerClassName = strtolower($classname);
                        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                            if (strtolower($method->class) == $lowerClassName) {
                                if ($method->name != "__construct")
                                    $methodNames[] = $method->name;
                            }
                        }
                        $Found[$classname] = $methodNames;
                    }
                }
            }
        } catch (Exception $e) {
            return false;
        }
        //pre($Found,true);
        return $Found;
    }

}
