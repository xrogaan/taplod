<?php
/**
 * @category Taplod
 * @package Taplod_Url
 * @copyright Copyright (c) 2009, Bellière Ludovic
 * @license http://opensource.org/licenses/mit-license.php MIT license
 */

/**
 * Retrieve data for the navigations
 *
 * @author xrogaan
 */
class Taplod_Pages extends ArrayObject {

    private $_use_include_path=false;

    public function __construct($data='pages.csv', $datatype='csv') {
        if ($data instanceof Taplod_Config) {
            $data = $data->toArray();
            foreach ($data as $pageName => $labelData) {
                $parentLabel = '';
                if (isset($labelData['parentLabel'])) {
                    $parentLabel = $labelData['parentLabel'];
                }
            }
        } elseif (!is_array($data)) {
            switch ($datatype) {
                case 'csv':
                    if (Taplod_Loader::fileExists($data)) {
                        $this->_use_include_path = true;
                        $pageDataPath = '';
                    } else {
                        if (!defined('APPLICATION_PATH')) {
                            trigger_error('Application path not found, please define it.',E_USER_WARNING);
                            return false;
                        }

                        $pageDataPath = realpath(APPLICATION_PATH . '../').'/';
                        if (Loader::fileExists($pageDataPath.$data)) {
                            throw new Taplod_Exception("$pageDataPath/$data doesn't exists.");
                        }

                    }

                    $h = fopen($pageDataPath.$data,'r', $this->_use_include_path);
                    if ($h !== false) {
                        while( ($data = fgetcsv($h, 1024, ';')) !== false) {
                            $parentLabel = '';
                            if (isset($data[2])) {
                                $parentLabel = $data[2];
                            }
                            self::__set($data[0],array('label' => $data[1], 'parentLabel' => $parentLabel));
                        }
                        fclose($h);
                    } else {
                        die('erro.');
                    }
                    break;
                case 'sqlite':
                    break;
            }
        }
    }

    public function getLabelFor($page) {
        if ($this->offsetExists($page)) {
            $data = $this->offsetGet($page);
            return $data['label'];
        }
        return false;
    }

    public function getLabelIfParent($page) {
        if ($this->offsetExists($page)) {
            $data = $this->offsetGet($page);
            return $data['parentLabel'];
        }
        return false;
    }

    public function set($tag,$pageData) {
        if (!$this->offsetExists($tag)) {
            $this->offsetSet($tag, $pageData);
            return true;
        }
        return false;
    }
    public function __get($tag) {
        if ($this->offsetExists($tag)) {
            return $this->offsetGet($tag);
        } else {
            require_once 'Taplod/Exception.php';
            throw new Taplod_Exception('No entry registered for '. $tag);
        }
    }

    public function __set($tag,$value) {
        return $this->set($tag,$value);
    }
    public function __isset($tag) {
        return $this->offsetExists($tag);
    }
}

